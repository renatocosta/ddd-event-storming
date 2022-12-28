<?php

if (!function_exists('defineDependenciesWith')) {

    function defineDependenciesWith($app, array $dependencies, array $defaultDependencies = []): array
    {

        $dependenciesLoader = [];

        $dependencies = array_merge($defaultDependencies, $dependencies);

        $dependenciesKeyNames = ['db', 'cache.store'];
        $skipKeys = ['skip_additional_handlers'];

        foreach ($dependencies as $dependencyClass => $dependency) {
            if (!isset($dependenciesLoader[$dependencyClass]) && !in_array($dependencyClass, $skipKeys)) {

                if (isset($dependencies[$dependencyClass]) && is_object($dependencies[$dependencyClass])) {
                    $dependenciesLoader[$dependencyClass] = $dependencies[$dependencyClass];
                } else if (in_array($dependency, $dependenciesKeyNames)) {
                    $dependenciesLoader[$dependencyClass] = $app[$dependency];
                } else {
                    $dependenciesLoader[$dependencyClass] =  $app->makeWith($dependencyClass, $dependenciesLoader);
                }
            }
        }

        return $dependenciesLoader;
    }
}
