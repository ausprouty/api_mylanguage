<?php

$appDirectory = 'C:/ampp82/htdocs/api_mylanguage/App';
echo "Checking directory: $appDirectory\n";
if (!is_dir($appDirectory)) {
    die("Directory does not exist: $appDirectory\n");
}

// Ensure autoloading works (adjust path if necessary)
require_once 'C:/ampp82/htdocs/api_mylanguage/Vendor/autoload.php';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDirectory));
$definitions = [];

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        $namespace = $matches[1];
    } else {
        $namespace = null;
    }

    if (preg_match('/class\s+(\w+)/', $content, $matches)) {
        $class = $namespace ? $namespace . '\\' . $matches[1] : $matches[1];

        try {
            $reflector = new ReflectionClass($class);

            // Skip non-instantiable classes (abstract, interfaces, or static-only)
            if (!$reflector->isInstantiable() || $reflector->isAbstract()) {
                continue;
            }

            $constructor = $reflector->getConstructor();
            if ($constructor) {
                $dependencies = array_map(
                    fn($param) => [
                        'name' => $param->getName(),
                        'type' => (string) $param->getType(),
                    ],
                    $constructor->getParameters()
                );

                $definitions[$class] = array_map(fn($dep) => $dep['type'], $dependencies);
            }
        } catch (ReflectionException $e) {
            echo "Skipping class $class: " . $e->getMessage() . "\n";
        }
    }
}

// Output as a PHP-DI configuration file
$configFile = $appDirectory . '/Configuration/php-di-definitions.php';
$phpDiConfig = "<?php\nreturn " . var_export($definitions, true) . ";";
file_put_contents($configFile, $phpDiConfig);

echo "PHP-DI configuration file generated: $configFile\n";
