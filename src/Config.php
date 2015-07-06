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
        return (isset(self::getInstance()->config[$keyPath])) ? self::getInstance()->config[$keyPath] : false;
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

    private function __clone()
    {
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

        // Убираем вложенность у конфига
        $this->config = $this->normalizeConfig($this->config);
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
    private function normalizeConfig($config, $baseKeyPath = '')
    {
        // Инициализируем результирующий массив
        $normalizedConfig = [];

        foreach ($config as $key => $value)
        {
            $keyPath = $baseKeyPath . $key;

            // Если значение - массив и при этом он ассоциативный - нормализуем его и мержим с текущими значениями конфига
            if (is_array($value) && self::isAssociativeArray($value))
            {
                $normalizedConfig = array_merge($normalizedConfig, $this->normalizeConfig($value, $keyPath . self::KEY_SEPARATOR));
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
