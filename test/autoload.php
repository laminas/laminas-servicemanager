<?php // phpcs:disable Generic.Files.LineLength.TooLong

if (PHP_VERSION_ID >= 80100) {
    require __DIR__ . '/TestAsset/laminas-code/ParameterReflection.php';
    require __DIR__ . '/TestAsset/laminas-code/MethodReflection.php';
    require __DIR__ . '/TestAsset/laminas-code/ClassReflection.php';

    class_alias(\Laminas\Code\Reflection\ParameterReflection::class, \Zend\Code\Reflection\ParameterReflection::class);
    class_alias(\Laminas\Code\Reflection\MethodReflection::class, \Zend\Code\Reflection\MethodReflection::class);
    class_alias(\Laminas\Code\Reflection\ClassReflection::class, \Zend\Code\Reflection\ClassReflection::class);
}
