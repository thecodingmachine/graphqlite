"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[9793],{19365:(e,n,r)=>{r.d(n,{A:()=>i});var a=r(96540),t=r(20053);const o={tabItem:"tabItem_Ymn6"};function i(e){let{children:n,hidden:r,className:i}=e;return a.createElement("div",{role:"tabpanel",className:(0,t.A)(o.tabItem,i),hidden:r},n)}},11470:(e,n,r)=>{r.d(n,{A:()=>$});var a=r(58168),t=r(96540),o=r(20053),i=r(23104),l=r(56347),s=r(57485),c=r(31682),u=r(89466);function p(e){return function(e){return t.Children.map(e,(e=>{if(!e||(0,t.isValidElement)(e)&&function(e){const{props:n}=e;return!!n&&"object"==typeof n&&"value"in n}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:n,label:r,attributes:a,default:t}}=e;return{value:n,label:r,attributes:a,default:t}}))}function d(e){const{values:n,children:r}=e;return(0,t.useMemo)((()=>{const e=n??p(r);return function(e){const n=(0,c.X)(e,((e,n)=>e.value===n.value));if(n.length>0)throw new Error(`Docusaurus error: Duplicate values "${n.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[n,r])}function h(e){let{value:n,tabValues:r}=e;return r.some((e=>e.value===n))}function m(e){let{queryString:n=!1,groupId:r}=e;const a=(0,l.W6)(),o=function(e){let{queryString:n=!1,groupId:r}=e;if("string"==typeof n)return n;if(!1===n)return null;if(!0===n&&!r)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return r??null}({queryString:n,groupId:r});return[(0,s.aZ)(o),(0,t.useCallback)((e=>{if(!o)return;const n=new URLSearchParams(a.location.search);n.set(o,e),a.replace({...a.location,search:n.toString()})}),[o,a])]}function y(e){const{defaultValue:n,queryString:r=!1,groupId:a}=e,o=d(e),[i,l]=(0,t.useState)((()=>function(e){let{defaultValue:n,tabValues:r}=e;if(0===r.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(n){if(!h({value:n,tabValues:r}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${n}" but none of its children has the corresponding value. Available values are: ${r.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return n}const a=r.find((e=>e.default))??r[0];if(!a)throw new Error("Unexpected error: 0 tabValues");return a.value}({defaultValue:n,tabValues:o}))),[s,c]=m({queryString:r,groupId:a}),[p,y]=function(e){let{groupId:n}=e;const r=function(e){return e?`docusaurus.tab.${e}`:null}(n),[a,o]=(0,u.Dv)(r);return[a,(0,t.useCallback)((e=>{r&&o.set(e)}),[r,o])]}({groupId:a}),g=(()=>{const e=s??p;return h({value:e,tabValues:o})?e:null})();(0,t.useLayoutEffect)((()=>{g&&l(g)}),[g]);return{selectedValue:i,selectValue:(0,t.useCallback)((e=>{if(!h({value:e,tabValues:o}))throw new Error(`Can't select invalid tab value=${e}`);l(e),c(e),y(e)}),[c,y,o]),tabValues:o}}var g=r(92303);const f={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:n,block:r,selectedValue:l,selectValue:s,tabValues:c}=e;const u=[],{blockElementScrollPositionUntilNextRender:p}=(0,i.a_)(),d=e=>{const n=e.currentTarget,r=u.indexOf(n),a=c[r].value;a!==l&&(p(n),s(a))},h=e=>{let n=null;switch(e.key){case"Enter":d(e);break;case"ArrowRight":{const r=u.indexOf(e.currentTarget)+1;n=u[r]??u[0];break}case"ArrowLeft":{const r=u.indexOf(e.currentTarget)-1;n=u[r]??u[u.length-1];break}}n?.focus()};return t.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,o.A)("tabs",{"tabs--block":r},n)},c.map((e=>{let{value:n,label:r,attributes:i}=e;return t.createElement("li",(0,a.A)({role:"tab",tabIndex:l===n?0:-1,"aria-selected":l===n,key:n,ref:e=>u.push(e),onKeyDown:h,onClick:d},i,{className:(0,o.A)("tabs__item",f.tabItem,i?.className,{"tabs__item--active":l===n})}),r??n)})))}function w(e){let{lazy:n,children:r,selectedValue:a}=e;const o=(Array.isArray(r)?r:[r]).filter(Boolean);if(n){const e=o.find((e=>e.props.value===a));return e?(0,t.cloneElement)(e,{className:"margin-top--md"}):null}return t.createElement("div",{className:"margin-top--md"},o.map(((e,n)=>(0,t.cloneElement)(e,{key:n,hidden:e.props.value!==a}))))}function v(e){const n=y(e);return t.createElement("div",{className:(0,o.A)("tabs-container",f.tabList)},t.createElement(b,(0,a.A)({},e,n)),t.createElement(w,(0,a.A)({},e,n)))}function $(e){const n=(0,g.A)();return t.createElement(v,(0,a.A)({key:String(n)},e))}},79111:(e,n,r)=>{r.r(n),r.d(n,{assets:()=>s,contentTitle:()=>i,default:()=>d,frontMatter:()=>o,metadata:()=>l,toc:()=>c});var a=r(58168),t=(r(96540),r(15680));r(67443),r(11470),r(19365);const o={id:"other-frameworks",title:"Getting started with any framework",sidebar_label:"Other frameworks / No framework"},i=void 0,l={unversionedId:"other-frameworks",id:"version-6.1/other-frameworks",title:"Getting started with any framework",description:"Installation",source:"@site/versioned_docs/version-6.1/other-frameworks.mdx",sourceDirName:".",slug:"/other-frameworks",permalink:"/docs/6.1/other-frameworks",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-6.1/other-frameworks.mdx",tags:[],version:"6.1",lastUpdatedBy:"Christophe Vergne",lastUpdatedAt:1715296507,formattedLastUpdatedAt:"May 9, 2024",frontMatter:{id:"other-frameworks",title:"Getting started with any framework",sidebar_label:"Other frameworks / No framework"},sidebar:"docs",previous:{title:"Universal service providers",permalink:"/docs/6.1/universal-service-providers"},next:{title:"Queries",permalink:"/docs/6.1/queries"}},s={},c=[{value:"Installation",id:"installation",level:2},{value:"Requirements",id:"requirements",level:2},{value:"Integration",id:"integration",level:2},{value:"GraphQLite context",id:"graphqlite-context",level:3},{value:"Minimal example",id:"minimal-example",level:2},{value:"PSR-15 Middleware",id:"psr-15-middleware",level:2},{value:"Example",id:"example",level:3}],u={toc:c},p="wrapper";function d(e){let{components:n,...r}=e;return(0,t.yg)(p,(0,a.A)({},u,r,{components:n,mdxType:"MDXLayout"}),(0,t.yg)("h2",{id:"installation"},"Installation"),(0,t.yg)("p",null,"Open a terminal in your current project directory and run:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-console"},"$ composer require thecodingmachine/graphqlite\n")),(0,t.yg)("h2",{id:"requirements"},"Requirements"),(0,t.yg)("p",null,"In order to bootstrap GraphQLite, you will need:"),(0,t.yg)("ul",null,(0,t.yg)("li",{parentName:"ul"},"A PSR-11 compatible container"),(0,t.yg)("li",{parentName:"ul"},"A PSR-16 cache")),(0,t.yg)("p",null,"Additionally, you will have to route the HTTP requests to the underlying GraphQL library."),(0,t.yg)("p",null,"GraphQLite relies on the ",(0,t.yg)("a",{parentName:"p",href:"http://webonyx.github.io/graphql-php/"},"webonyx/graphql-php")," library internally.\nThis library plays well with PSR-7 requests and we also provide a ",(0,t.yg)("a",{parentName:"p",href:"#psr-15-middleware"},"PSR-15 middleware"),"."),(0,t.yg)("h2",{id:"integration"},"Integration"),(0,t.yg)("p",null,"Webonyx/graphql-php library requires a ",(0,t.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/type-system/schema/"},"Schema")," in order to resolve\nGraphQL queries. We provide a ",(0,t.yg)("inlineCode",{parentName:"p"},"SchemaFactory")," class to create such a schema:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\SchemaFactory;\n\n// $cache is a PSR-16 compatible cache\n// $container is a PSR-11 compatible container\n$factory = new SchemaFactory($cache, $container);\n$factory->addControllerNamespace('App\\\\Controllers\\\\')\n        ->addTypeNamespace('App\\\\');\n\n$schema = $factory->createSchema();\n")),(0,t.yg)("p",null,"You can now use this schema with ",(0,t.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/getting-started/#hello-world"},"Webonyx GraphQL facade"),"\nor the ",(0,t.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/executing-queries/#using-server"},"StandardServer class"),"."),(0,t.yg)("p",null,"The ",(0,t.yg)("inlineCode",{parentName:"p"},"SchemaFactory")," class also comes with a number of methods that you can use to customize your GraphQLite settings."),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},'// Configure an authentication service (to resolve the @Logged annotations).\n$factory->setAuthenticationService(new VoidAuthenticationService());\n// Configure an authorization service (to resolve the @Right annotations).\n$factory->setAuthorizationService(new VoidAuthorizationService());\n// Change the naming convention of GraphQL types globally.\n$factory->setNamingStrategy(new NamingStrategy());\n// Add a custom type mapper.\n$factory->addTypeMapper($typeMapper);\n// Add a custom type mapper using a factory to create it.\n// Type mapper factories are useful if you need to inject the "recursive type mapper" into your type mapper constructor.\n$factory->addTypeMapperFactory($typeMapperFactory);\n// Add a root type mapper.\n$factory->addRootTypeMapper($rootTypeMapper);\n// Add a parameter mapper.\n$factory->addParameterMapper($parameterMapper);\n// Add a query provider. These are used to find queries and mutations in the application.\n$factory->addQueryProvider($queryProvider);\n// Add a query provider using a factory to create it.\n// Query provider factories are useful if you need to inject the "fields builder" into your query provider constructor.\n$factory->addQueryProviderFactory($queryProviderFactory);\n// Set a default InputType validator service to handle validation on all `Input` annotated types\n$factory->setInputTypeValidator($validator);\n// Add custom options to the Webonyx underlying Schema.\n$factory->setSchemaConfig($schemaConfig);\n// Configures the time-to-live for the GraphQLite cache. Defaults to 2 seconds in dev mode.\n$factory->setGlobTtl(2);\n// Enables prod-mode (cache settings optimized for best performance).\n// This is a shortcut for `$schemaFactory->setGlobTtl(null)`\n$factory->prodMode();\n// Enables dev-mode (this is the default mode: cache settings optimized for best developer experience).\n// This is a shortcut for `$schemaFactory->setGlobTtl(2)`\n$factory->devMode();\n')),(0,t.yg)("h3",{id:"graphqlite-context"},"GraphQLite context"),(0,t.yg)("p",null,'Webonyx allows you pass a "context" object when running a query.\nFor some GraphQLite features to work (namely: the prefetch feature), GraphQLite needs you to initialize the Webonyx context\nwith an instance of the ',(0,t.yg)("inlineCode",{parentName:"p"},"TheCodingMachine\\GraphQLite\\Context\\Context")," class."),(0,t.yg)("p",null,"For instance:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Context\\Context;\n\n$result = GraphQL::executeQuery($schema, $query, null, new Context(), $variableValues);\n")),(0,t.yg)("h2",{id:"minimal-example"},"Minimal example"),(0,t.yg)("p",null,"The smallest working example using no framework is:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"<?php\nuse GraphQL\\GraphQL;\nuse GraphQL\\Type\\Schema;\nuse TheCodingMachine\\GraphQLite\\SchemaFactory;\nuse TheCodingMachine\\GraphQLite\\Context\\Context;\n\n// $cache is a PSR-16 compatible cache.\n// $container is a PSR-11 compatible container.\n$factory = new SchemaFactory($cache, $container);\n$factory->addControllerNamespace('App\\\\Controllers\\\\')\n        ->addTypeNamespace('App\\\\');\n\n$schema = $factory->createSchema();\n\n$rawInput = file_get_contents('php://input');\n$input = json_decode($rawInput, true);\n$query = $input['query'];\n$variableValues = isset($input['variables']) ? $input['variables'] : null;\n\n$result = GraphQL::executeQuery($schema, $query, null, new Context(), $variableValues);\n$output = $result->toArray();\n\nheader('Content-Type: application/json');\necho json_encode($output);\n")),(0,t.yg)("h2",{id:"psr-15-middleware"},"PSR-15 Middleware"),(0,t.yg)("p",null,"When using a framework, you will need a way to route your HTTP requests to the ",(0,t.yg)("inlineCode",{parentName:"p"},"webonyx/graphql-php")," library."),(0,t.yg)("p",null,"If the framework you are using is compatible with PSR-15 (like Slim PHP or Zend-Expressive / Laminas), GraphQLite\ncomes with a PSR-15 middleware out of the box."),(0,t.yg)("p",null,"In order to get an instance of this middleware, you can use the ",(0,t.yg)("inlineCode",{parentName:"p"},"Psr15GraphQLMiddlewareBuilder")," builder class:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"// $schema is an instance of the GraphQL schema returned by SchemaFactory::createSchema (see previous chapter)\n$builder = new Psr15GraphQLMiddlewareBuilder($schema);\n\n$middleware = $builder->createMiddleware();\n\n// You can now inject your middleware in your favorite PSR-15 compatible framework.\n// For instance:\n$zendMiddlewarePipe->pipe($middleware);\n")),(0,t.yg)("p",null,"The builder offers a number of setters to modify its behaviour:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"$builder->setUrl(\"/graphql\"); // Modify the URL endpoint (defaults to /graphql)\n\n$config = $builder->getConfig(); // Returns a Webonyx ServerConfig object.\n// Define your own formatter and error handlers for Webonyx.\n$config->setErrorFormatter([ExceptionHandler::class, 'errorFormatter']);\n$config->setErrorsHandler([ExceptionHandler::class, 'errorHandler']);\n\n$builder->setConfig($config);\n\n$builder->setResponseFactory(new ResponseFactory()); // Set a PSR-18 ResponseFactory (not needed if you are using zend-framework/zend-diactoros ^2\n$builder->setStreamFactory(new StreamFactory()); // Set a PSR-18 StreamFactory (not needed if you are using zend-framework/zend-diactoros ^2\n$builder->setHttpCodeDecider(new HttpCodeDecider()); // Set a class in charge of deciding the HTTP status code based on the response.\n")),(0,t.yg)("h3",{id:"example"},"Example"),(0,t.yg)("p",null,"In this example, we will focus on getting a working version of GraphQLite using:"),(0,t.yg)("ul",null,(0,t.yg)("li",{parentName:"ul"},(0,t.yg)("a",{parentName:"li",href:"https://docs.zendframework.com/zend-stratigility/"},"Zend Stratigility")," as a PSR-15 server"),(0,t.yg)("li",{parentName:"ul"},(0,t.yg)("inlineCode",{parentName:"li"},"symfony/cache ")," for the PSR-16 cache")),(0,t.yg)("p",null,"The choice of the libraries is really up to you. You can adapt it based on your needs."),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-json",metastring:'title="composer.json"',title:'"composer.json"'},'{\n  "autoload": {\n    "psr-4": {\n      "App\\\\": "src/"\n    }\n  },\n  "require": {\n    "thecodingmachine/graphqlite": "^4",\n    "zendframework/zend-diactoros": "^2",\n    "zendframework/zend-stratigility": "^3",\n    "zendframework/zend-httphandlerrunner": "^1.0",\n    "symfony/cache": "^4.2"\n  },\n  "minimum-stability": "dev",\n  "prefer-stable": true\n}\n')),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="index.php"',title:'"index.php"'},"<?php\n\nuse Laminas\\Diactoros\\Response;\nuse Laminas\\Diactoros\\ServerRequest;\nuse Laminas\\Diactoros\\ServerRequestFactory;\nuse Zend\\HttpHandlerRunner\\Emitter\\SapiStreamEmitter;\nuse Zend\\Stratigility\\Middleware\\ErrorResponseGenerator;\nuse Zend\\Stratigility\\MiddlewarePipe;\nuse Laminas\\Diactoros\\Server;\nuse Zend\\HttpHandlerRunner\\RequestHandlerRunner;\n\nrequire_once __DIR__ . '/vendor/autoload.php';\n\n$container = require 'config/container.php';\n\n$serverRequestFactory = [ServerRequestFactory::class, 'fromGlobals'];\n\n$errorResponseGenerator = function (Throwable $e) {\n    $generator = new ErrorResponseGenerator();\n    return $generator($e, new ServerRequest(), new Response());\n};\n\n$runner = new RequestHandlerRunner(\n    $container->get(MiddlewarePipe::class),\n    new SapiStreamEmitter(),\n    $serverRequestFactory,\n    $errorResponseGenerator\n);\n$runner->run();\n")),(0,t.yg)("p",null,"Here we are initializing a Zend ",(0,t.yg)("inlineCode",{parentName:"p"},"RequestHandler")," (it receives requests) and we pass it to a Zend Stratigility ",(0,t.yg)("inlineCode",{parentName:"p"},"MiddlewarePipe"),".\nThis ",(0,t.yg)("inlineCode",{parentName:"p"},"MiddlewarePipe")," comes from the container declared in the ",(0,t.yg)("inlineCode",{parentName:"p"},"config/container.php")," file:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="config/container.php"',title:'"config/container.php"'},"<?php\n\nuse GraphQL\\Type\\Schema;\nuse TheCodingMachine\\GraphQLite\\Containers\\LazyContainer;\nuse Psr\\Container\\ContainerInterface;\nuse Psr\\SimpleCache\\CacheInterface;\nuse Symfony\\Component\\Cache\\Simple\\ApcuCache;\nuse TheCodingMachine\\GraphQLite\\Http\\Psr15GraphQLMiddlewareBuilder;\nuse TheCodingMachine\\GraphQLite\\SchemaFactory;\nuse Zend\\Stratigility\\MiddlewarePipe;\n\n// LazyContainer is a minimalist PSR-11 container.\nreturn new LazyContainer([\n    MiddlewarePipe::class => function(ContainerInterface $container) {\n        $pipe = new MiddlewarePipe();\n        $pipe->pipe($container->get(WebonyxGraphqlMiddleware::class));\n        return $pipe;\n    },\n    // The WebonyxGraphqlMiddleware is a PSR-15 compatible\n    // middleware that exposes Webonyx schemas.\n    WebonyxGraphqlMiddleware::class => function(ContainerInterface $container) {\n        $builder = new Psr15GraphQLMiddlewareBuilder($container->get(Schema::class));\n        return $builder->createMiddleware();\n    },\n    CacheInterface::class => function() {\n        return new ApcuCache();\n    },\n    Schema::class => function(ContainerInterface $container) {\n        // The magic happens here. We create a schema using GraphQLite SchemaFactory.\n        $factory = new SchemaFactory($container->get(CacheInterface::class), $container);\n        $factory->addControllerNamespace('App\\\\Controllers\\\\');\n        $factory->addTypeNamespace('App\\\\');\n        return $factory->createSchema();\n    }\n]);\n")),(0,t.yg)("p",null,"Now, we need to add a first query and therefore create a controller.\nThe application will look into the ",(0,t.yg)("inlineCode",{parentName:"p"},"App\\Controllers")," namespace for GraphQLite controllers."),(0,t.yg)("p",null,"It assumes that the container has an entry whose name is the controller's fully qualified class name."),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="src/Controllers/MyController.php"',title:'"src/Controllers/MyController.php"'},"namespace App\\Controllers;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass MyController\n{\n    #[Query]\n    public function hello(string $name): string\n    {\n        return 'Hello '.$name;\n    }\n}\n")),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="config/container.php"',title:'"config/container.php"'},"use App\\Controllers\\MyController;\n\nreturn new LazyContainer([\n    // ...\n\n    // We declare the controller in the container.\n    MyController::class => function() {\n        return new MyController();\n    },\n]);\n")),(0,t.yg)("p",null,"And we are done! You can now test your query using your favorite GraphQL client."))}d.isMDXComponent=!0}}]);