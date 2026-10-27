<?php

namespace Config;

/**
 * Paths
 *
 * Holds the paths that are used by the system to
 * locate the main directories, app, system, etc.
 *
 * Modifying these allows you to restructure your application,
 * share a system folder between multiple applications, and more.
 *
 * All paths are relative to the project's root folder.
 *
 * NOTE: This class is required prior to Autoloader instantiation,
 *       and does not extend BaseConfig.
 */
class Paths
{
    /**
     * ---------------------------------------------------------------
     * SYSTEM FOLDER NAME
     * ---------------------------------------------------------------
     *
     * This must contain the name of your "system" folder. Include
     * the path if the folder is not in the same directory as this file.
     */
    public string $systemDirectory = __DIR__ . '/../../system';

    /**
     * ---------------------------------------------------------------
     * APPLICATION FOLDER NAME
     * ---------------------------------------------------------------
     *
     * If you want this front controller to use a different "app"
     * folder than the default one you can set its name here. The folder
     * can also be renamed or relocated anywhere on your server. If
     * you do, use a full server path.
     *
     * @see http://codeigniter.com/user_guide/general/managing_apps.html
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * ---------------------------------------------------------------
     * WRITABLE DIRECTORY NAME
     * ---------------------------------------------------------------
     *
     * This variable must contain the name of your "writable" directory.
     * The writable directory allows you to group all directories that
     * need write permission to a single place that can be tucked away
     * for maximum security, keeping it out of the app and/or
     * system directories.
     */
    public string $writableDirectory = __DIR__ . '/../../writable';

    public function __construct()
    {
        // Di lingkungan Vercel / Serverless AWS Lambda, hanya direktori /tmp yang memiliki izin tulis
        if (getenv('VERCEL') || getenv('VERCEL_URL') || getenv('AWS_LAMBDA_FUNCTION_NAME') || (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false) || (!empty($_ENV['VERCEL']))) {
            $this->writableDirectory = '/tmp/writable';
            if (!is_dir('/tmp/writable')) {
                @mkdir('/tmp/writable/cache', 0777, true);
                @mkdir('/tmp/writable/logs', 0777, true);
                @mkdir('/tmp/writable/session', 0777, true);
                @mkdir('/tmp/writable/uploads/datasets', 0777, true);
                @mkdir('/tmp/writable/debugbar', 0777, true);
            }
        }
    }

    /**
     * TESTS DIRECTORY NAME
     * ---------------------------------------------------------------
     *
     * This variable must contain the name of your "tests" directory.
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * ---------------------------------------------------------------
     * VIEW DIRECTORY NAME
     * ---------------------------------------------------------------
     *
     * This variable must contain the name of the directory that
     * contains the view files used by your application. By
     * default this is in `app/Views`. This value
     * is used when no value is provided to `Services::renderer()`.
     */
    public string $viewDirectory = __DIR__ . '/../Views';

    /**
     * ---------------------------------------------------------------
     * ENVIRONMENT DIRECTORY NAME
     * ---------------------------------------------------------------
     *
     * This variable must contain the name of the directory where
     * the .env file is located.
     * Please consider security implications when changing this
     * value - the directory should not be publicly accessible.
     */
    public string $envDirectory = __DIR__ . '/../../';
}
