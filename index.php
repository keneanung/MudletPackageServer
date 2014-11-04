<?php

use MudletPackageServer\Classes\Propel\Package;
use MudletPackageServer\Classes\Propel\PackageQuery;
use Symfony\Component\HttpFoundation\Request;

require_once "vendor/autoload.php";

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    "twig.path" => __DIR__ . "/templates"
));

$app->register(new Propel\Silex\PropelServiceProvider());

$env = getenv('APP_ENV') ?: 'dev';
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . "/config/config.json"));
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . "/config/$env.json"));

$app->get("/api/list", function(Request $request) use($app){
    $packageArray = PackageQuery::create()->find();
    $tmpArray = array();
    /** @var Package $package */
    foreach($packageArray as $package){
        $pkg = $package->toArray();
        $pkg["url"] = sprintf("%s://%s%s/packages/%s.dat",
            $request->getScheme(),  $request->getHost(), $request->getBaseUrl(), $package->getName());
        $tmpArray[] = $pkg;
    }
    return $app->json($tmpArray);
});

$app->get("/", function() use($app){
    return $app["twig"]->render("index.twig", array(
        "packages" => PackageQuery::create()->find()
    ));
});

$app->run();