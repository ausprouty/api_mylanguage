<?php
$directory = __DIR__ . '/../'; // Adjust the path as needed
$progressFile = __DIR__ . '/php-di-progress.json'; // File to save progress
$outputFile = __DIR__ . '/../Configuration/di/di-all.php';

echo "Checking directory: $directory\n";
if (!is_dir($directory)) {
    die("Directory does not exist: $directory\n");
}

require __DIR__ . '/../../Vendor/autoload.php';

// Load previous progress if the file exists
$definitions = file_exists($progressFile)
    ? json_decode(file_get_contents($progressFile), true)
    : [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

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

        // Skip if already processed
        if (isset($definitions[$class])) {
            echo "Skipping: Already processed -> $class\n";
            continue;
        }

        // Check if the class exists before reflecting
        if (!class_exists($class)) {
            echo "Skipping: Class not found -> $class\n";
            $definitions[$class] = null; // Record it to avoid re-checking
            file_put_contents($progressFile, json_encode($definitions, JSON_PRETTY_PRINT));
            continue;
        }

        try {
            $reflector = new ReflectionClass($class);
            if ($reflector->isInstantiable()) {
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
                } else {
                    $definitions[$class] = [];
                }
            }
        } catch (Exception $e) {
            echo "Error processing $class: " . $e->getMessage() . "\n";
            $definitions[$class] = null; // Record the error for this class
        }

        // Save progress after processing each class
        file_put_contents($progressFile, json_encode($definitions, JSON_PRETTY_PRINT));
        echo "Saved progress for: $class\n";
    }
}

// Write final PHP-DI configuration file

$phpDiConfig = "<?php\nreturn " . var_export($definitions, true) . ";";
file_put_contents($outputFile, $phpDiConfig);

echo "PHP-DI configuration file generated: $outputFile\n";
