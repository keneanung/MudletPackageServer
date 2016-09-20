<?php

use MudletPackageServer\Classes\Propel\Package;
use MudletPackageServer\Classes\Propel\PackageQuery;
use MudletPackageServer\Classes\Propel\User;
use MudletPackageServer\Classes\Propel\UserQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

require_once "vendor/autoload.php";

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    "twig.path" => __DIR__ . "/templates"
));

$app->register(new Propel\Silex\PropelServiceProvider());
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'private' => array(
            'pattern' => "^/",
            'form' => array( 'login_path' => 'login', 'check_path' => '/private/login_check'),
            'logout' => array( 'logout_path' => '/private/logout'),
            'users' => $app->share(function() use($app){
                return new \MudletPackageServer\Classes\UserProvider();
            }),
            'anonymous' => true,
        ),
    ),
    'security.access_rules' => array(
        array('^/private', 'IS_AUTHENTICATED_FULLY')
    )
));
$app->register(new Silex\Provider\SessionServiceProvider());

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
    if($app["security"]->isGranted("IS_AUTHENTICATED_FULLY")){
        $user = $app["security"]->getToken()->getUsername();
        $loggedIn = true;
    }else{
        $user = "";
        $loggedIn = false;
    }
    return $app["twig"]->render("index.twig", array(
        "packages" => PackageQuery::create()->find(),
        "loggedIn" => $loggedIn,
        "user"     => $user
    ));
})->bind("index");

$app->get("/register", function() use($app){
    return $app["twig"]->render("register.twig");
})->bind("register");

$app->post("/register", function(Request $request) use($app){;

    $user = new User();
    $user->setName($request->get("username"));
    $user->setEmail($request->get("email"));

    /** @var PasswordEncoderInterface $encoder */
    $encoder = $app['security.encoder_factory']->getEncoder($user);

    $user->setPassword($encoder->encodePassword($request->get("password"), $user->getSalt()));
    $user->setVerified(0);
    $user->setCreatedOn(time());

    $user->save();

    $verify_string = urlencode($user->getVerifyString());
    $safe_email = urlencode($user->getEmail());
    $verify_url = $app['url_generator']->generate('verify_user');
    $mail_body = <<<_MAIL_
To {$user->getName()}:
Please click on the following link to verify your account creation:

$verify_url?email=$safe_email&verify_string=$verify_string

If you do not verify your account in the next seven days, it will be deleted.
_MAIL_;

    mail($user->getEmail(), "User Verification", $mail_body, "FROM: noreply@{{ $request->getHttpHost() }}");

    return $app->redirect($app['url_generator']->generate("index"));

})->bind("register_post");

$app->get("/verify_user", function(Request $request) use($app){
    $users = UserQuery::create()
        ->filterByEmail($request->get("email"))
        ->filterByVerifyString($request->get("verify_string"))
        ->filterByVerified(0)
        ->find();

    if(count($users) == 0){
        $message = "Your user does not exist or is not in need to be verified.";
    }else{
        $users[0]->setVerified(1);
        $users[0]->save();
        $message = "Your user has been successfully verified.";
    }

    return $app["twig"]->render("verify_user.twig", array(
        "message" => $message
    ));

})->bind("verify_user");

$app->get("/login", function(Request $request) use($app){
    return $app["twig"]->render("login.twig", array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind("login");

$app->get("/private/administer", function(Request $request) use($app){
    $user = $app["security"]->getToken()->getUsername();
    $packages = PackageQuery::create()
        ->findByAuthor($user);
    return $app["twig"]->render("administer.twig", array(
        'user'     => $user,
        'packages' => $packages,
        'error'    => $request->get("error")
    ));
})->bind("administer");

$app->get("/private/edit_package", function(Request $request) use ($app){

    $arguments = $request->query;

    if($arguments->has("name_error") || $arguments->has("version_error")
        || $arguments->has("description_error") || $arguments->has("file_error") ){
        $url_args = array(
            "name_error"        => $arguments->get("name_error"),
            "version_error"     => $arguments->get("version_error"),
            "description_error" => $arguments->get("description_error"),
            "file_error"        => $arguments->get("file_error"),
        );
    }else{
        $url_args = array(
            "name_error"        => "",
            "version_error"     => "",
            "description_error" => "",
            "file_error"        => "",
        );
    }

    if($request->get("package") != null){
        $mode = "modify";
        $package = PackageQuery::create()
            ->findOneByName($request->get("package"));
        if($package == null){
            return $app->redirect($app["url_generator"]->generate("administer", array(
                'error' => "Package " . $request->get("package") . " not found."
            )));
        }
    }else{
        $mode = "create";
        $package = array(
            'Name'        => '',
            'Version'     => '',
            'Description' => ''
        );
    }

    $argument_array = array_merge($url_args, array(
        'package' => $package,
        'mode'    => $mode
    ));

    return $app["twig"]->render("edit_package.twig", $argument_array);
})->bind("edit_package");

$app->post("/private/edit_package", function(Request $request) use ($app){

    $name_error = "";
    $name_valid = TRUE;
    if (strlen($request->get("name")) == 0) {
        $name_valid = FALSE;
        $name_error = "You need to give your package a name.";
    }elseif(strlen($request->get("name")) > 50){
        $name_valid = FALSE;
        $name_error = "The package name may not be longer than 50 characters.";
    }elseif(preg_match("/^\w+$/", $request->get("name")) != 1){
        $name_valid = FALSE;
        $name_error = "The package name may only contain alphanumerical characters (no spaces, no special chars)";
    }

    $version_error = "";
    $version_valid = TRUE;
    if(strlen($request->get("version")) <= 0){
        $version_valid = FALSE;
        $version_error = "You must assign a version to your package";
    }elseif(strlen($request->get("version")) > 15){
        $version_valid = FALSE;
        $version_error = "The package version may not be longer than 15 characters.";
    }elseif(preg_match("/^\d+\.\d+\.\d+$/", $request->get("version")) == 1){
        $version_valid = FALSE;
        $version_error = "The version may only contain 3 parts of digits only, delimited by periods.";
    }

    $description_error = "";
    $description_valid = TRUE;
    if (strlen($request->get("description")) <= 0){
        $description_valid = FALSE;
        $description_error = "You must give an description for your package..";
    }elseif(strlen($request->get("description")) > 120){
        $description_valid = FALSE;
        $description_error = "The description may only contain up to 120 characters.";
    }

    $file_error = "";
    $file_valid = TRUE;
    $extension = $request->files->get("file")->getExtension();
    $mimetype = $request->files->get("file")->getMimeType();

    if($extension != "xml" && $extension != "mpackage" && $extension != "zip"){
        $file_valid = FALSE;
        $file_error = "Only file extensions 'zip', 'xml' and 'mpackage' are allowed.";
    }elseif($request->files->get("file")->getSize() < 20000000){
        $file_valid = FALSE;
        $file_error = "Files may not exceed 20000000 Bytes.";
    }elseif($mimetype != "application/octet-stream" && $mimetype != "text/xml" && $mimetype != "application/zip"){
        $file_valid = TRUE;
        $file_error = "Wrong mimetype for the file.";
    }

    if($name_valid && $version_valid && $description_valid && $file_valid){
        $request->files->get("file")->move("packages", $request->get("name") . ".dat");
        $package = new Package();
        $package->setName($request->get("name"));
        $package->setVersion($request->get("version"));
        $package->setDescription($request->get("description"));
        $package->setExtension($extension);
        $package->setAuthor($app["security"]->getToken()->getUsername());
        $package->save();
        return $app["twig"]->render("modify_package_success.twig");
    }else{
        return $app->redirect($app["url_generator"]->generate("edit_package", array(
            "name_error"        => $name_error,
            "version_error"     => $version_error,
            "description_error" => $description_error,
            "file_error"        => $file_error
        )));
    }

})->bind(("edit_package_post"));

$app->run();