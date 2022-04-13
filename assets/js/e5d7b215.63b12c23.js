"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[153],{3905:function(e,r,t){t.d(r,{Zo:function(){return s},kt:function(){return h}});var n=t(67294);function i(e,r,t){return r in e?Object.defineProperty(e,r,{value:t,enumerable:!0,configurable:!0,writable:!0}):e[r]=t,e}function a(e,r){var t=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);r&&(n=n.filter((function(r){return Object.getOwnPropertyDescriptor(e,r).enumerable}))),t.push.apply(t,n)}return t}function o(e){for(var r=1;r<arguments.length;r++){var t=null!=arguments[r]?arguments[r]:{};r%2?a(Object(t),!0).forEach((function(r){i(e,r,t[r])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(t)):a(Object(t)).forEach((function(r){Object.defineProperty(e,r,Object.getOwnPropertyDescriptor(t,r))}))}return e}function p(e,r){if(null==e)return{};var t,n,i=function(e,r){if(null==e)return{};var t,n,i={},a=Object.keys(e);for(n=0;n<a.length;n++)t=a[n],r.indexOf(t)>=0||(i[t]=e[t]);return i}(e,r);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);for(n=0;n<a.length;n++)t=a[n],r.indexOf(t)>=0||Object.prototype.propertyIsEnumerable.call(e,t)&&(i[t]=e[t])}return i}var l=n.createContext({}),c=function(e){var r=n.useContext(l),t=r;return e&&(t="function"==typeof e?e(r):o(o({},r),e)),t},s=function(e){var r=c(e.components);return n.createElement(l.Provider,{value:r},e.children)},u={inlineCode:"code",wrapper:function(e){var r=e.children;return n.createElement(n.Fragment,{},r)}},d=n.forwardRef((function(e,r){var t=e.components,i=e.mdxType,a=e.originalType,l=e.parentName,s=p(e,["components","mdxType","originalType","parentName"]),d=c(t),h=i,m=d["".concat(l,".").concat(h)]||d[h]||u[h]||a;return t?n.createElement(m,o(o({ref:r},s),{},{components:t})):n.createElement(m,o({ref:r},s))}));function h(e,r){var t=arguments,i=r&&r.mdxType;if("string"==typeof e||i){var a=t.length,o=new Array(a);o[0]=d;var p={};for(var l in r)hasOwnProperty.call(r,l)&&(p[l]=r[l]);p.originalType=e,p.mdxType="string"==typeof e?e:i,o[1]=p;for(var c=2;c<a;c++)o[c]=t[c];return n.createElement.apply(null,o)}return n.createElement.apply(null,t)}d.displayName="MDXCreateElement"},78869:function(e,r,t){t.r(r),t.d(r,{contentTitle:function(){return l},default:function(){return d},frontMatter:function(){return p},metadata:function(){return c},toc:function(){return s}});var n=t(87462),i=t(63366),a=(t(67294),t(3905)),o=["components"],p={id:"universal-service-providers",title:"Getting started with a framework compatible with container-interop/service-provider",sidebar_label:"Universal service providers"},l=void 0,c={unversionedId:"universal-service-providers",id:"universal-service-providers",title:"Getting started with a framework compatible with container-interop/service-provider",description:"container-interop/service-provider is an experimental project",source:"@site/docs/universal-service-providers.md",sourceDirName:".",slug:"/universal-service-providers",permalink:"/docs/next/universal-service-providers",editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/universal-service-providers.md",tags:[],version:"current",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1649831959,formattedLastUpdatedAt:"4/13/2022",frontMatter:{id:"universal-service-providers",title:"Getting started with a framework compatible with container-interop/service-provider",sidebar_label:"Universal service providers"},sidebar:"docs",previous:{title:"Laravel package",permalink:"/docs/next/laravel-package"},next:{title:"Other frameworks / No framework",permalink:"/docs/next/other-frameworks"}},s=[{value:"Installation",id:"installation",children:[],level:2},{value:"Requirements",id:"requirements",children:[],level:2},{value:"Integration",id:"integration",children:[],level:2},{value:"Sample usage",id:"sample-usage",children:[],level:2}],u={toc:s};function d(e){var r=e.components,t=(0,i.Z)(e,o);return(0,a.kt)("wrapper",(0,n.Z)({},u,t,{components:r,mdxType:"MDXLayout"}),(0,a.kt)("p",null,(0,a.kt)("a",{parentName:"p",href:"https://github.com/container-interop/service-provider/"},"container-interop/service-provider")," is an experimental project\naiming to bring interoperability between framework module systems."),(0,a.kt)("p",null,"If your framework is compatible with ",(0,a.kt)("a",{parentName:"p",href:"https://github.com/container-interop/service-provider/"},"container-interop/service-provider"),",\nGraphQLite comes with a service provider that you can leverage."),(0,a.kt)("h2",{id:"installation"},"Installation"),(0,a.kt)("p",null,"Open a terminal in your current project directory and run:"),(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-console"},"$ composer require thecodingmachine/graphqlite-universal-service-provider\n")),(0,a.kt)("h2",{id:"requirements"},"Requirements"),(0,a.kt)("p",null,"In order to bootstrap GraphQLite, you will need:"),(0,a.kt)("ul",null,(0,a.kt)("li",{parentName:"ul"},"A PSR-16 cache")),(0,a.kt)("p",null,"Additionally, you will have to route the HTTP requests to the underlying GraphQL library."),(0,a.kt)("p",null,"GraphQLite relies on the ",(0,a.kt)("a",{parentName:"p",href:"http://webonyx.github.io/graphql-php/"},"webonyx/graphql-php")," library internally.\nThis library plays well with PSR-7 requests and we provide a ",(0,a.kt)("a",{parentName:"p",href:"/docs/next/other-frameworks"},"PSR-15 middleware"),"."),(0,a.kt)("h2",{id:"integration"},"Integration"),(0,a.kt)("p",null,"Webonyx/graphql-php library requires a ",(0,a.kt)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/type-system/schema/"},"Schema")," in order to resolve\nGraphQL queries. The service provider provides this ",(0,a.kt)("inlineCode",{parentName:"p"},"Schema")," class."),(0,a.kt)("p",null,(0,a.kt)("a",{parentName:"p",href:"https://github.com/thecodingmachine/graphqlite-universal-service-provider"},"Checkout the the service-provider documentation")),(0,a.kt)("h2",{id:"sample-usage"},"Sample usage"),(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-json",metastring:'title="composer.json"',title:'"composer.json"'},'{\n  "require": {\n    "mnapoli/simplex": "^0.5",\n    "thecodingmachine/graphqlite-universal-service-provider": "^3",\n    "thecodingmachine/symfony-cache-universal-module": "^1"\n  },\n  "minimum-stability": "dev",\n  "prefer-stable": true\n}\n')),(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-php",metastring:'title="index.php"',title:'"index.php"'},"<?php\nuse Simplex\\Container;\nuse TheCodingMachine\\GraphQLite\\Http\\Psr15GraphQLMiddlewareBuilder;\nuse TheCodingMachine\\GraphQLite\\Schema;\nuse TheCodingMachine\\SymfonyCacheServiceProvider;\nuse TheCodingMachine\\DoctrineAnnotationsServiceProvider;\nuse TheCodingMachine\\GraphQLiteServiceProvider;\n\n$container = new Container([\n    new SymfonyCacheServiceProvider(),\n    new DoctrineAnnotationsServiceProvider,\n    new GraphQLiteServiceProvider()]);\n$container->set('graphqlite.namespace.types', ['App\\\\Types']);\n$container->set('graphqlite.namespace.controllers', ['App\\\\Controllers']);\n\n$schema = $container->get(Schema::class);\n\n// or if you want the PSR-15 middleware:\n\n$middleware = $container->get(Psr15GraphQLMiddlewareBuilder::class);\n")))}d.isMDXComponent=!0}}]);