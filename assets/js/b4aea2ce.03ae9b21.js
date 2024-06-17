"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5138],{68781:(e,a,n)=>{n.r(a),n.d(a,{assets:()=>p,contentTitle:()=>i,default:()=>c,frontMatter:()=>l,metadata:()=>o,toc:()=>s});var r=n(58168),t=(n(96540),n(15680));n(67443);const l={id:"laravel-package",title:"Getting started with Laravel",sidebar_label:"Laravel package",original_id:"laravel-package"},i=void 0,o={unversionedId:"laravel-package",id:"version-3.0/laravel-package",title:"Getting started with Laravel",description:"The GraphQLite-Laravel package is compatible with Laravel 5.x.",source:"@site/versioned_docs/version-3.0/laravel-package.md",sourceDirName:".",slug:"/laravel-package",permalink:"/docs/3.0/laravel-package",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/laravel-package.md",tags:[],version:"3.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1718658882,formattedLastUpdatedAt:"Jun 17, 2024",frontMatter:{id:"laravel-package",title:"Getting started with Laravel",sidebar_label:"Laravel package",original_id:"laravel-package"},sidebar:"version-3.0/docs",previous:{title:"Symfony bundle",permalink:"/docs/3.0/symfony-bundle"},next:{title:"Universal service providers",permalink:"/docs/3.0/universal_service_providers"}},p={},s=[{value:"Installation",id:"installation",level:2},{value:"Adding GraphQL DevTools",id:"adding-graphql-devtools",level:2}],d={toc:s},g="wrapper";function c(e){let{components:a,...n}=e;return(0,t.yg)(g,(0,r.A)({},d,n,{components:a,mdxType:"MDXLayout"}),(0,t.yg)("p",null,"The GraphQLite-Laravel package is compatible with ",(0,t.yg)("strong",{parentName:"p"},"Laravel 5.x"),"."),(0,t.yg)("h2",{id:"installation"},"Installation"),(0,t.yg)("p",null,"Open a terminal in your current project directory and run:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-console"},"$ composer require thecodingmachine/graphqlite-laravel\n")),(0,t.yg)("p",null,"If you want to publish the configuration (in order to edit it), run:"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-console"},"$ php artisan vendor:publish --provider=TheCodingMachine\\GraphQLite\\Laravel\\Providers\\GraphQLiteServiceProvider\n")),(0,t.yg)("p",null,"You can then configure the library by editing ",(0,t.yg)("inlineCode",{parentName:"p"},"config/graphqlite.php"),"."),(0,t.yg)("p",null,(0,t.yg)("strong",{parentName:"p"},"config/graphqlite.php")),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-php"},"<?php\n\nuse GraphQL\\Error\\Debug;\n\nreturn [\n    /*\n     |--------------------------------------------------------------------------\n     | GraphQLite Configuration\n     |--------------------------------------------------------------------------\n     |\n     | Use this configuration to customize the namespace of the controllers and\n     | types.\n     | These namespaces must be autoloadable from Composer.\n     | GraphQLite will find the path of the files based on composer.json settings.\n     |\n     | You can put a single namespace, or an array of namespaces.\n     |\n     */\n    'controllers' => 'App\\\\Http\\\\Controllers',\n    'types' => 'App\\\\',\n    'debug' => Debug::RETHROW_UNSAFE_EXCEPTIONS,\n    'uri' => '/graphql'\n];\n")),(0,t.yg)("p",null,"The debug parameters are detailed in the ",(0,t.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/error-handling/"},"documentation of the Webonyx GraphQL library"),"\nwhich is used internally by GraphQLite."),(0,t.yg)("h2",{id:"adding-graphql-devtools"},"Adding GraphQL DevTools"),(0,t.yg)("p",null,"GraphQLite does not include additional GraphQL tooling, such as the GraphiQL editor.\nTo integrate a web UI to query your GraphQL endpoint with your Laravel installation,\nwe recommend installing GraphQL Playground"),(0,t.yg)("pre",null,(0,t.yg)("code",{parentName:"pre",className:"language-console"},"$ composer require mll-lab/laravel-graphql-playground\n")),(0,t.yg)("p",null,"You can also use any external client with GraphQLite, make sure to point it to the URL defined in the config (",(0,t.yg)("inlineCode",{parentName:"p"},"'/graphql'")," by default)."))}c.isMDXComponent=!0}}]);