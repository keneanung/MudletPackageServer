<?php

use MudletPackageServer\Classes\Package;
use Symfony\Component\HttpFoundation\Request;

require_once "vendor/autoload.php";

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    "twig.path" => __DIR__ . "/templates"
));

$env = getenv('APP_ENV') ?: 'dev';
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . "/config/config.json"));
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . "/config/$env.json"));

$con=mysqli_connect($app["database"]["server"], $app["database"]["user"], $app["database"]["password"],
    $app["database"]["database"]);

// Check connection
if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$app->get("/api/list", function(Request $request) use($app, $con){
    $packageArray = Package::GetPackages($con);
    $tmpArray = array();
    foreach($packageArray as $package){
        $pkg = $package->toArray();
        $pkg["author"] = $package->Author->Name;
        $pkg["url"]    = sprintf("%s://%s%s/packages/%s.dat",
            $request->getScheme(),  $request->getHost(), $request->getBaseUrl(), $package->Name);
        $tmpArray[] = $pkg;
    }
    return $app->json($tmpArray);
});

$app->get("/", function() use($app, $con){
    return $app["twig"]->render("index.twig", array(
        "packages" => Package::GetPackages($con)
    ));
});

$app->run();