<?php namespace JsonConfig;

/**
 * Class Config
 *
 * Статический класс для чтения конфигов в JSON-формате
 *
 * @package JsonConfig
 */
class Config
{
    // Символ, который необходим для вложенности ключей
    const KEY_SEPARATOR = '.';

    /** @var Config */
    private static $instance = null;

    /** @var array  */
    private $config = [];
    private $normalizedConfig = [];

    /**
     * Функция устанавливает настройки для конфига
     *
     * @param string $pathToConfig
     *
     * @throws \Exception
     */
    public static function setup($pathToConfig)
    {
        self::getInstance()->loadConfigFromFile($pathToConfig);
    }

    /**
     * Получение значения параметра из конфига
     *
     * @param string $keyPath
     *
     * @return bool
     */
    public static function get($keyPath)
    {
        $keyPath = strtolower($keyPath);
        $configInstance = self::getInstance();

        if (isset($configInstance->normalizedConfig[$keyPath]))
        {
            return $configInstance->normalizedConfig[$keyPath];
        }
        else
        {
            $keyParts = explode(self::KEY_SEPARATOR, $keyPath);
            $config = $configInstance->config;

            while (!empty($keyParts) && isset($config[$keyParts[0]]))
            {
                $config = $config[$keyParts[0]];
                array_shift($keyParts);
            }

            if (empty($keyParts))
            {
                return $config;
            }
        }

        return false;
    }

    /**
     * @return Config
     */
    private static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    /**
     * Функция загружает параметры конфига из файла
     *
     * @param string $pathToConfig
     *
     * @throws \Exception
     */
    private function loadConfigFromFile($pathToConfig)
    {
        // Проверяем существование файла
        if (!file_exists($pathToConfig))
        {
            throw new \Exception('Файл конфига не найден');
        }

        // Читаем и парсим файл
        $this->config = json_decode(file_get_contents($pathToConfig), true);

        // Если не удалось распарсить - кидаем ошибку
        if (empty($this->config))
        {
            throw new \Exception('Не удается распарсить JSON');
        }

        // Загружаем значения из переменных окружения.
        $this->config = $this->loadEnv($this->config);

        // Убираем вложенность у конфига
        $this->normalizedConfig = self::normalizeConfig($this->config);
    }

    /**
     * Функция заменяет значения в конфиги переменными из окружения
     *
     * @param array  $config      Конфиг
     * @param string $baseKeyPath Вложенность конфига
     *
     * @return array
     */
    private function loadEnv(array $config, string $baseKeyPath = '') : array
    {
        foreach ($config as $key => $value) {
            $keyPath = $baseKeyPath . $key;

            if (is_array($value) === true && self::isAssociativeArray($value)) {
                $config[$key] = $this->loadEnv($value, $keyPath . self::KEY_SEPARATOR);
                continue;
            }

            $config[$key] = $this->getEnv($keyPath, $value);
        }

        return $config;
    }

    /**
     * Функция получает значение переменной окружения для параметра в конфиге
     *
     * @param string $keyPath Название параметра в конфиге (например: urls.base.host)
     * @param mixed  $value   Значение конфига
     *
     * @return array|bool|false|float|int|mixed|string
     */
    private function getEnv(string $keyPath, $value)
    {
        $envKey = str_replace(['.', ':', '-'], '_', strtoupper($keyPath));

        $env = $_ENV[$envKey] ?? $_SERVER[$envKey] ?? getenv($envKey);

        // Если в переменных окружения значение не найдено, возвращаем значение.
        if ($env === false) {
            return $value;
        }

        // Если значение параметра конфига является массивом, то и в переменной окружения будет передан массив,
        // в случае с переменными окружения это json.
        if (is_array($value) === true) {
            $env = json_decode($env, true);
        }

        // Приведение строк к скалярным типам.
        if ($env === 'false') {
            $value = false;
        } else if ($env === 'true') {
            $value = true;
        } else if (is_numeric($env) === true) {
            // int к int, float к float.
            $value = (((string) (int) $env === $env) ? (int) $env : (float) $env);
        } else {
            $value = $env;
        }

        return $value;
    }

    /**
     * Функция убирает вложенность у массива конфигов, переводя его в массив с одинарной вложенностью
     *
     * Это делается для того, что бы быстрее выполнять доступ к элементам конфига
     *
     * @param array $config
     * @param string $baseKeyPath
     *
     * @return array
     */
    private static function normalizeConfig($config, $baseKeyPath = '')
    {
        // Инициализируем результирующий массив
        $normalizedConfig = [];

        foreach ($config as $key => $value)
        {
            $keyPath = $baseKeyPath . $key;

            // Если значение - массив и при этом он ассоциативный - нормализуем его и мержим с текущими значениями конфига
            if (is_array($value) && self::isAssociativeArray($value))
            {
                $normalizedConfig = array_merge($normalizedConfig, self::normalizeConfig($value, $keyPath . self::KEY_SEPARATOR));
            }
            else
            {
                $normalizedConfig[strtolower($keyPath)] = $value;
            }
        }

        return $normalizedConfig;
    }

    /**
     * Функция выполняет проверку, является ли массив ассоциативным или нет
     *
     * @param array $array
     *
     * @return bool
     */
    private static function isAssociativeArray($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
