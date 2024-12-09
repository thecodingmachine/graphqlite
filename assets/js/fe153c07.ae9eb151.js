"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[8059],{16949:(e,n,a)=>{a.r(n),a.d(n,{assets:()=>s,contentTitle:()=>i,default:()=>h,frontMatter:()=>o,metadata:()=>l,toc:()=>p});var r=a(58168),t=(a(96540),a(15680));a(67443);const o={id:"other-frameworks",title:"Getting started with any framework",sidebar_label:"Other frameworks / No framework",original_id:"other-frameworks"},i=void 0,l={unversionedId:"other-frameworks",id:"version-3.0/other-frameworks",title:"Getting started with any framework",description:"If you are using Symfony 4.x, checkout the Symfony bundle.",source:"@site/versioned_docs/version-3.0/other_frameworks.mdx",sourceDirName:".",slug:"/other-frameworks",permalink:"/docs/3.0/other-frameworks",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/other_frameworks.mdx",tags:[],version:"3.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1733781388,formattedLastUpdatedAt:"Dec 9, 2024",frontMatter:{id:"other-frameworks",title:"Getting started with any framework",sidebar_label:"Other frameworks / No framework",original_id:"other-frameworks"},sidebar:"version-3.0/docs",previous:{title:"Universal service providers",permalink:"/docs/3.0/universal_service_providers"},next:{title:"Queries",permalink:"/docs/3.0/queries"}},s={},p=[{value:"Installation",id:"installation",level:2},{value:"Requirements",id:"requirements",level:2},{value:"Integration",id:"integration",level:2},{value:"Minimal example",id:"minimal-example",level:2},{value:"Advanced example",id:"advanced-example",level:2}],c={toc:p},u="wrapper";function h(e){let{components:n,...o}=e;return(0,t.yg)(u,(0,r.A)({},c,o,{components:n,mdxType:"MDXLayout"}),(0,t.yg)("p",null,"If you are using ",(0,t.yg)("strong",{parentName:"p"},"Symfony 4.x"),", checkout the ",(0,t.yg)("a",{parentName:"p",href:"/docs/3.0/symfony-bundle"},"Symfony bundle"),"."),(0,t.yg)("h2",{id:"installation"},"Installation"),(0,t.yg)("p",null,"Open a terminal in your current project directory and run:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-console"},"$ composer require thecodingmachine/graphqlite\n")),(0,t.yg)("h2",{id:"requirements"},"Requirements"),(0,t.yg)("p",null,"In order to bootstrap GraphQLite, you will need:"),(0,t.yg)("ul",null,(0,t.yg)("li",{parentName:"ul"},"A PSR-11 compatible container"),(0,t.yg)("li",{parentName:"ul"},"A PSR-16 cache")),(0,t.yg)("p",null,"Additionally, you will have to route the HTTP requests to the underlying GraphQL library."),(0,t.yg)("p",null,"GraphQLite relies on the ",(0,t.yg)("a",{parentName:"p",href:"http://webonyx.github.io/graphql-php/"},"webonyx/graphql-php")," library internally.\nThis library plays well with PSR-7 requests and there is a ",(0,t.yg)("a",{parentName:"p",href:"https://github.com/phps-cans/psr7-middleware-graphql"},"PSR-15 middleware available"),"."),(0,t.yg)("h2",{id:"integration"},"Integration"),(0,t.yg)("p",null,"Webonyx/graphql-php library requires a ",(0,t.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/type-system/schema/"},"Schema")," in order to resolve\nGraphQL queries. We provide a ",(0,t.yg)("inlineCode",{parentName:"p"},"SchemaFactory")," class to create such a schema:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\SchemaFactory;\n\n// $cache is a PSR-16 compatible cache\n// $container is a PSR-11 compatible container\n$factory = new SchemaFactory($cache, $container);\n$factory->addControllerNamespace('App\\\\Controllers\\\\')\n        ->addTypeNamespace('App\\\\');\n\n$schema = $factory->createSchema();\n")),(0,t.yg)("p",null,"You can now use this schema with ",(0,t.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/getting-started/#hello-world"},"Webonyx GraphQL facade"),"\nor the ",(0,t.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/executing-queries/#using-server"},"StandardServer class"),"."),(0,t.yg)("p",null,"The ",(0,t.yg)("inlineCode",{parentName:"p"},"SchemaFactory")," class also comes with a number of methods that you can use to customize your GraphQLite settings."),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"// Configure an authentication service (to resolve the @Logged annotations).\n$factory->setAuthenticationService(new VoidAuthenticationService());\n// Configure an authorization service (to resolve the @Right annotations).\n$factory->setAuthorizationService(new VoidAuthorizationService());\n// Change the naming convention of GraphQL types globally.\n$factory->setNamingStrategy(new NamingStrategy());\n// Add a custom type mapper.\n$factory->addTypeMapper($typeMapper);\n// Add custom options to the Webonyx underlying Schema.\n$factory->setSchemaConfig($schemaConfig);\n")),(0,t.yg)("h2",{id:"minimal-example"},"Minimal example"),(0,t.yg)("p",null,"The smallest working example using no framework is:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"<?php\nuse GraphQL\\GraphQL;\nuse GraphQL\\Type\\Schema;\nuse TheCodingMachine\\GraphQLite\\SchemaFactory;\n\n// $cache is a PSR-16 compatible cache.\n// $container is a PSR-11 compatible container.\n$factory = new SchemaFactory($cache, $container);\n$factory->addControllerNamespace('App\\\\Controllers\\\\')\n        ->addTypeNamespace('App\\\\');\n\n$schema = $factory->createSchema();\n\n$rawInput = file_get_contents('php://input');\n$input = json_decode($rawInput, true);\n$query = $input['query'];\n$variableValues = isset($input['variables']) ? $input['variables'] : null;\n\n$result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);\n$output = $result->toArray();\n\nheader('Content-Type: application/json');\necho json_encode($output);\n")),(0,t.yg)("h2",{id:"advanced-example"},"Advanced example"),(0,t.yg)("p",null,"When using a framework, you will need a way to route your HTTP requests to the ",(0,t.yg)("inlineCode",{parentName:"p"},"webonyx/graphql-php")," library.\nBy chance, it plays well with PSR-7 requests and there is a PSR-15 middleware available."),(0,t.yg)("p",null,"In this example, we will focus on getting a working version of GraphQLite using:"),(0,t.yg)("ul",null,(0,t.yg)("li",{parentName:"ul"},(0,t.yg)("a",{parentName:"li",href:"https://docs.zendframework.com/zend-stratigility/"},"Zend Stratigility")," as a PSR-7 server"),(0,t.yg)("li",{parentName:"ul"},(0,t.yg)("inlineCode",{parentName:"li"},"phps-cans/psr7-middleware-graphql")," to route PSR-7 requests to the GraphQL engine"),(0,t.yg)("li",{parentName:"ul"},(0,t.yg)("inlineCode",{parentName:"li"},"mouf/picotainer")," (a micro-container) for the PSR-11 container"),(0,t.yg)("li",{parentName:"ul"},(0,t.yg)("inlineCode",{parentName:"li"},"symfony/cache ")," for the PSR-16 cache")),(0,t.yg)("p",null,"The choice of the libraries is really up to you. You can adapt it based on your needs."),(0,t.yg)("p",null,(0,t.yg)("strong",{parentName:"p"},"composer.json")),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-json"},'{\n  "autoload": {\n    "psr-4": {\n      "App\\\\": "src/"\n    }\n  },\n  "require": {\n    "thecodingmachine/graphqlite": "^3",\n    "phps-cans/psr7-middleware-graphql": "^0.2",\n    "middlewares/payload": "^2.1",\n    "zendframework/zend-diactoros": "^2",\n    "zendframework/zend-stratigility": "^3",\n    "zendframework/zend-httphandlerrunner": "^1.0",\n    "mouf/picotainer": "^1.1",\n    "symfony/cache": "^4.2"\n  },\n  "minimum-stability": "dev",\n  "prefer-stable": true\n}\n')),(0,t.yg)("p",null,(0,t.yg)("strong",{parentName:"p"},"index.php")),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"<?php\n\nuse Laminas\\Diactoros\\Response;\nuse Laminas\\Diactoros\\ServerRequest;\nuse Laminas\\Diactoros\\ServerRequestFactory;\nuse Zend\\HttpHandlerRunner\\Emitter\\SapiStreamEmitter;\nuse Zend\\Stratigility\\Middleware\\ErrorResponseGenerator;\nuse Zend\\Stratigility\\MiddlewarePipe;\nuse Laminas\\Diactoros\\Server;\nuse Zend\\HttpHandlerRunner\\RequestHandlerRunner;\n\nrequire_once __DIR__ . '/vendor/autoload.php';\n\n$container = require 'config/container.php';\n\n$serverRequestFactory = [ServerRequestFactory::class, 'fromGlobals'];\n\n$errorResponseGenerator = function (Throwable $e) {\n    $generator = new ErrorResponseGenerator();\n    return $generator($e, new ServerRequest(), new Response());\n};\n\n$runner = new RequestHandlerRunner(\n    $container->get(MiddlewarePipe::class),\n    new SapiStreamEmitter(),\n    $serverRequestFactory,\n    $errorResponseGenerator\n);\n$runner->run();\n")),(0,t.yg)("p",null,"Here we are initializing a Zend ",(0,t.yg)("inlineCode",{parentName:"p"},"RequestHandler")," (it receives requests) and we pass it to a Zend Stratigility ",(0,t.yg)("inlineCode",{parentName:"p"},"MiddlewarePipe"),".\nThis ",(0,t.yg)("inlineCode",{parentName:"p"},"MiddlewarePipe")," comes from the container declared in the ",(0,t.yg)("inlineCode",{parentName:"p"},"config/container.php")," file:"),(0,t.yg)("p",null,(0,t.yg)("strong",{parentName:"p"},"config/container.php")),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"<?php\n\nuse GraphQL\\Server\\StandardServer;\nuse GraphQL\\Type\\Schema;\nuse Mouf\\Picotainer\\Picotainer;\nuse PsCs\\Middleware\\Graphql\\WebonyxGraphqlMiddleware;\nuse Psr\\Container\\ContainerInterface;\nuse Psr\\SimpleCache\\CacheInterface;\nuse Symfony\\Component\\Cache\\Simple\\ApcuCache;\nuse TheCodingMachine\\GraphQLite\\SchemaFactory;\nuse Laminas\\Diactoros\\ResponseFactory;\nuse Laminas\\Diactoros\\StreamFactory;\nuse Zend\\Stratigility\\MiddlewarePipe;\n\n// Picotainer is a minimalist PSR-11 container.\nreturn new Picotainer([\n    MiddlewarePipe::class => function(ContainerInterface $container) {\n        $pipe = new MiddlewarePipe();\n        // JsonPayload converts JSON body into a parser PHP array.\n        $pipe->pipe(new JsonPayload());\n        $pipe->pipe($container->get(WebonyxGraphqlMiddleware::class));\n        return $pipe;\n    },\n    // The WebonyxGraphqlMiddleware is a PSR-15 compatible\n    // middleware that exposes Webonyx schemas.\n    WebonyxGraphqlMiddleware::class => function(ContainerInterface $container) {\n        return new WebonyxGraphqlMiddleware(\n            $container->get(StandardServer::class),\n            new ResponseFactory(),\n            new StreamFactory()\n        );\n    },\n    StandardServer::class => function(ContainerInterface $container) {\n        return new StandardServer([\n            'schema' => $container->get(Schema::class)\n        ]);\n    },\n    CacheInterface::class => function() {\n        return new ApcuCache();\n    },\n    Schema::class => function(ContainerInterface $container) {\n        // The magic happens here. We create a schema using GraphQLite SchemaFactory.\n        $factory = new SchemaFactory($container->get(CacheInterface::class), $container);\n        $factory->addControllerNamespace('App\\\\Controllers\\\\');\n        $factory->addTypeNamespace('App\\\\');\n        return $factory->createSchema();\n    }\n]);\n")),(0,t.yg)("p",null,"Now, we need to add a first query and therefore create a controller.\nThe application will look into the ",(0,t.yg)("inlineCode",{parentName:"p"},"App\\Controllers")," namespace for GraphQLite controllers."),(0,t.yg)("p",null,"It assumes that the container has an entry whose name is the controller's fully qualified class name."),(0,t.yg)("p",null,(0,t.yg)("strong",{parentName:"p"},"src/Controllers/MyController.php")),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controllers;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass MyController\n{\n    /**\n     * @Query\n     */\n    public function hello(string $name): string\n    {\n        return 'Hello '.$name;\n    }\n}\n")),(0,t.yg)("p",null,(0,t.yg)("strong",{parentName:"p"},"config/container.php")),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"use App\\Controllers\\MyController;\n\nreturn new Picotainer([\n    // ...\n\n    // We declare the controller in the container.\n    MyController::class => function() {\n        return new MyController();\n    },\n]);\n")),(0,t.yg)("p",null,"And we are done! You can now test your query using your favorite GraphQL client."),(0,t.yg)("p",null,(0,t.yg)("img",{src:a(67258).A,width:"1132",height:"352"})))}h.isMDXComponent=!0},67258:(e,n,a)=>{a.d(n,{A:()=>r});const r=a.p+"assets/images/query1-5a22bbe2398efcc725ea571a07ff2c9b.png"}}]);