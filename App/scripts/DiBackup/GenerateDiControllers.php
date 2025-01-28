<?php
$directory = __DIR__ . '/../Controllers'; // Only look through the Factories folder
$outputFile = __DIR__ . '/../Configuration/di/di-controllers.php';
 // File to save the final definitions

echo "Checking directory: $directory\n";
if (!is_dir($directory)) {
    die("Directory does not exist: $directory\n");
}

require __DIR__ . '/../../Vendor/autoload.php';

$definitions = []; // Initialize an empty array to hold definitions

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

        // Check if the class exists before reflecting
        if (!class_exists($class)) {
            echo "Skipping: Class not found -> $class\n";
            continue;
        }

        try {
            $reflector = new ReflectionClass($class);
            if ($reflector->isInstantiable() || $reflector->isAbstract()) {
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

        echo "Processed: $class\n";
    }
}

// Write final PHP-DI configuration file for Factories
$phpDiConfig = "<?php\nreturn [\n";
foreach ($definitions as $class => $dependencies) {
    $phpDiConfig .= "    '$class' => DI\\autowire()->constructor(\n";
    if (!empty($dependencies)) {
        $phpDiConfig .= implode(",\n", array_map(fn($dep) => "        DI\\get('$dep')", $dependencies));
    }
    $phpDiConfig .= "\n    ),\n";
}
$phpDiConfig .= "];\n";

file_put_contents($outputFile, $phpDiConfig);

echo "PHP-DI configuration file generated: $outputFile\n";
