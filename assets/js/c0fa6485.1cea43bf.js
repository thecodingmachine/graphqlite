"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[1989],{3905:(t,e,n)=>{n.d(e,{Zo:()=>u,kt:()=>m});var a=n(67294);function r(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}function o(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(t);e&&(a=a.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,a)}return n}function i(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?o(Object(n),!0).forEach((function(e){r(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):o(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function l(t,e){if(null==t)return{};var n,a,r=function(t,e){if(null==t)return{};var n,a,r={},o=Object.keys(t);for(a=0;a<o.length;a++)n=o[a],e.indexOf(n)>=0||(r[n]=t[n]);return r}(t,e);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);for(a=0;a<o.length;a++)n=o[a],e.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(t,n)&&(r[n]=t[n])}return r}var s=a.createContext({}),p=function(t){var e=a.useContext(s),n=e;return t&&(n="function"==typeof t?t(e):i(i({},e),t)),n},u=function(t){var e=p(t.components);return a.createElement(s.Provider,{value:e},t.children)},c={inlineCode:"code",wrapper:function(t){var e=t.children;return a.createElement(a.Fragment,{},e)}},d=a.forwardRef((function(t,e){var n=t.components,r=t.mdxType,o=t.originalType,s=t.parentName,u=l(t,["components","mdxType","originalType","parentName"]),d=p(n),m=r,b=d["".concat(s,".").concat(m)]||d[m]||c[m]||o;return n?a.createElement(b,i(i({ref:e},u),{},{components:n})):a.createElement(b,i({ref:e},u))}));function m(t,e){var n=arguments,r=e&&e.mdxType;if("string"==typeof t||r){var o=n.length,i=new Array(o);i[0]=d;var l={};for(var s in e)hasOwnProperty.call(e,s)&&(l[s]=e[s]);l.originalType=t,l.mdxType="string"==typeof t?t:r,i[1]=l;for(var p=2;p<o;p++)i[p]=n[p];return a.createElement.apply(null,i)}return a.createElement.apply(null,n)}d.displayName="MDXCreateElement"},20210:(t,e,n)=>{n.r(e),n.d(e,{assets:()=>s,contentTitle:()=>i,default:()=>c,frontMatter:()=>o,metadata:()=>l,toc:()=>p});var a=n(87462),r=(n(67294),n(3905));const o={id:"doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",sidebar_label:"Annotations VS Attributes",original_id:"doctrine-annotations-attributes"},i=void 0,l={unversionedId:"doctrine-annotations-attributes",id:"version-4.1/doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",description:"GraphQLite is heavily relying on the concept of annotations (also called attributes in PHP 8+).",source:"@site/versioned_docs/version-4.1/doctrine_annotations_attributes.md",sourceDirName:".",slug:"/doctrine-annotations-attributes",permalink:"/docs/4.1/doctrine-annotations-attributes",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.1/doctrine_annotations_attributes.md",tags:[],version:"4.1",lastUpdatedBy:"bladl",lastUpdatedAt:1657811576,formattedLastUpdatedAt:"7/14/2022",frontMatter:{id:"doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",sidebar_label:"Annotations VS Attributes",original_id:"doctrine-annotations-attributes"},sidebar:"version-4.1/docs",previous:{title:"Migrating",permalink:"/docs/4.1/migrating"},next:{title:"Annotations reference",permalink:"/docs/4.1/annotations_reference"}},s={},p=[{value:"Doctrine annotations",id:"doctrine-annotations",level:2},{value:"PHP 8 attributes",id:"php-8-attributes",level:2}],u={toc:p};function c(t){let{components:e,...n}=t;return(0,r.kt)("wrapper",(0,a.Z)({},u,n,{components:e,mdxType:"MDXLayout"}),(0,r.kt)("p",null,"GraphQLite is heavily relying on the concept of annotations (also called attributes in PHP 8+)."),(0,r.kt)("h2",{id:"doctrine-annotations"},"Doctrine annotations"),(0,r.kt)("div",{class:"alert alert--warning"},(0,r.kt)("strong",null,"Deprecated!")," Doctrine annotations are deprecated in favor of native PHP 8 attributes. Support will be dropped in GraphQLite 5.0"),(0,r.kt)("p",null,'Historically, attributes were not available in PHP and PHP developers had to "trick" PHP to get annotation support.\nThis was the purpose of the ',(0,r.kt)("a",{parentName:"p",href:"https://www.doctrine-project.org/projects/doctrine-annotations/en/latest/index.html"},"doctrine/annotation")," library."),(0,r.kt)("p",null,"Using Doctrine annotations, you write annotations in your docblocks:"),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n/**\n * @Type\n */\nclass MyType\n{\n}\n")),(0,r.kt)("p",null,"Please note that:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"The annotation is added in a ",(0,r.kt)("strong",{parentName:"li"},"docblock"),' (a comment starting with "',(0,r.kt)("inlineCode",{parentName:"li"},"/**"),'")'),(0,r.kt)("li",{parentName:"ul"},"The ",(0,r.kt)("inlineCode",{parentName:"li"},"Type")," part is actually a class. It must be declared in the ",(0,r.kt)("inlineCode",{parentName:"li"},"use")," statements at the top of your file.")),(0,r.kt)("div",{class:"alert alert--info"},(0,r.kt)("strong",null,"Heads up!"),"Some IDEs provide support for Doctrine annotations:",(0,r.kt)("ul",null,(0,r.kt)("li",null,"PhpStorm via the ",(0,r.kt)("a",{href:"https://plugins.jetbrains.com/plugin/7320-php-annotations"},"PHP Annotations Plugin")),(0,r.kt)("li",null,"Eclipse via the ",(0,r.kt)("a",{href:"https://marketplace.eclipse.org/content/symfony-plugin"},"Symfony 2 Plugin")),(0,r.kt)("li",null,"Netbeans has native support")),(0,r.kt)("p",null,"We strongly recommend using an IDE that has Doctrine annotations support.")),(0,r.kt)("h2",{id:"php-8-attributes"},"PHP 8 attributes"),(0,r.kt)("p",null,'Starting with PHP 8, PHP got native annotations support. They are actually called "attributes" in the PHP world.'),(0,r.kt)("p",null,"The same code can be written this way:"),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n#[Type]\nclass MyType\n{\n}\n")),(0,r.kt)("p",null,"GraphQLite v4.1+ has support for PHP 8 attributes."),(0,r.kt)("p",null,"The Doctrine annotation class and the PHP 8 attribute class is ",(0,r.kt)("strong",{parentName:"p"},"the same")," (so you will be using the same ",(0,r.kt)("inlineCode",{parentName:"p"},"use")," statement at the top of your file)."),(0,r.kt)("p",null,"They support the same attributes too."),(0,r.kt)("p",null,"A few notable differences:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"PHP 8 attributes do not support nested attributes (unlike Doctrine annotations). This means there is no equivalent to the ",(0,r.kt)("inlineCode",{parentName:"li"},"annotations")," attribute of ",(0,r.kt)("inlineCode",{parentName:"li"},"@MagicField")," and ",(0,r.kt)("inlineCode",{parentName:"li"},"@SourceField"),"."),(0,r.kt)("li",{parentName:"ul"},'PHP 8 attributes can be written at the parameter level. Any attribute targeting a "parameter" must be written at the parameter level.')),(0,r.kt)("p",null,"Let's take an example with the ",(0,r.kt)("a",{parentName:"p",href:"/docs/4.1/autowiring"},(0,r.kt)("inlineCode",{parentName:"a"},"#Autowire")," attribute"),":"),(0,r.kt)("p",null,(0,r.kt)("strong",{parentName:"p"},"PHP 7+")),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre"},'/**\n * @Field\n * @Autowire(for="$productRepository")\n */\npublic function getProduct(ProductRepository $productRepository) : Product {\n    //...\n}\n')),(0,r.kt)("p",null,(0,r.kt)("strong",{parentName:"p"},"PHP 8")),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre"},"#[Field]\npublic function getProduct(#[Autowire] ProductRepository $productRepository) : Product {\n    //...\n}\n")))}c.isMDXComponent=!0}}]);