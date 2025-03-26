"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[6369],{83269:(t,e,n)=>{n.r(e),n.d(e,{assets:()=>u,contentTitle:()=>r,default:()=>d,frontMatter:()=>o,metadata:()=>s,toc:()=>l});var a=n(58168),i=(n(96540),n(15680));n(67443);const o={id:"doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",sidebar_label:"Annotations VS Attributes"},r=void 0,s={unversionedId:"doctrine-annotations-attributes",id:"version-8.0.0/doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",description:"GraphQLite is heavily relying on the concept of annotations (also called attributes in PHP 8+).",source:"@site/versioned_docs/version-8.0.0/doctrine-annotations-attributes.mdx",sourceDirName:".",slug:"/doctrine-annotations-attributes",permalink:"/docs/doctrine-annotations-attributes",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-8.0.0/doctrine-annotations-attributes.mdx",tags:[],version:"8.0.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1742957246,formattedLastUpdatedAt:"Mar 26, 2025",frontMatter:{id:"doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",sidebar_label:"Annotations VS Attributes"},sidebar:"docs",previous:{title:"Migrating",permalink:"/docs/migrating"},next:{title:"Attributes reference",permalink:"/docs/annotations-reference"}},u={},l=[{value:"Doctrine annotations",id:"doctrine-annotations",level:2},{value:"PHP 8 attributes",id:"php-8-attributes",level:2},{value:"Migrating from Doctrine annotations to PHP 8 attributes",id:"migrating-from-doctrine-annotations-to-php-8-attributes",level:2}],p={toc:l},c="wrapper";function d(t){let{components:e,...n}=t;return(0,i.yg)(c,(0,a.A)({},p,n,{components:e,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"GraphQLite is heavily relying on the concept of annotations (also called attributes in PHP 8+)."),(0,i.yg)("h2",{id:"doctrine-annotations"},"Doctrine annotations"),(0,i.yg)("div",{class:"alert alert--danger"},(0,i.yg)("strong",null,"Unsupported!")," Doctrine annotations are replaced in favor of native PHP 8 attributes."),(0,i.yg)("h2",{id:"php-8-attributes"},"PHP 8 attributes"),(0,i.yg)("p",null,'Starting with PHP 8, PHP got native annotations support. They are actually called "attributes" in the PHP world.'),(0,i.yg)("p",null,"The same code can be written this way:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n#[Type]\nclass MyType\n{\n}\n")),(0,i.yg)("p",null,"GraphQLite v4.1+ has support for PHP 8 attributes."),(0,i.yg)("p",null,"The Doctrine annotation class and the PHP 8 attribute class is ",(0,i.yg)("strong",{parentName:"p"},"the same")," (so you will be using the same ",(0,i.yg)("inlineCode",{parentName:"p"},"use")," statement at the top of your file)."),(0,i.yg)("p",null,"They support the same attributes too."),(0,i.yg)("p",null,"A few notable differences:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},'PHP 8 attributes can be written at the parameter level. Any attribute targeting a "parameter" must be written at the parameter level.')),(0,i.yg)("p",null,"Let's take an example with the ",(0,i.yg)("a",{parentName:"p",href:"/docs/autowiring"},(0,i.yg)("inlineCode",{parentName:"a"},"#Autowire")," attribute"),":"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"#[Field]\npublic function getProduct(#[Autowire] ProductRepository $productRepository) : Product {\n    //...\n}\n")),(0,i.yg)("h2",{id:"migrating-from-doctrine-annotations-to-php-8-attributes"},"Migrating from Doctrine annotations to PHP 8 attributes"),(0,i.yg)("p",null,"The good news is that you can easily migrate from Doctrine annotations to PHP 8 attributes using the amazing, ",(0,i.yg)("a",{parentName:"p",href:"https://github.com/rectorphp/rector"},"Rector library"),".  To do so, you'll want to use the following rector configuration:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="rector.php"',title:'"rector.php"'},"<?php\n\nuse Rector\\Core\\Configuration\\Option;\nuse Rector\\Php80\\Rector\\Class_\\AnnotationToAttributeRector;\nuse Rector\\Php80\\ValueObject\\AnnotationToAttribute;\nuse Rector\\Set\\ValueObject\\SetList;\nuse Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ContainerConfigurator;\nuse TheCodingMachine\\GraphQLite\\Annotations as GraphQLite;\n\nreturn static function (ContainerConfigurator $containerConfigurator): void {\n    // Here we can define, what sets of rules will be applied\n    // tip: use \"SetList\" class to autocomplete sets\n    // $containerConfigurator->import(SetList::CODE_QUALITY);\n\n    // Set parameters\n    $parameters = $containerConfigurator->parameters();\n    $parameters->set(Option::PATHS, [\n        __DIR__ . '/src',\n        __DIR__ . '/tests',\n    ]);\n\n    $services = $containerConfigurator->services();\n\n    // @Validate and @Assertion are part of other libraries, include if necessary\n    $services->set(AnnotationToAttributeRector::class)\n        ->configure([\n            new AnnotationToAttribute(GraphQLite\\Query::class),\n            new AnnotationToAttribute(GraphQLite\\Mutation::class),\n            new AnnotationToAttribute(GraphQLite\\Type::class),\n            new AnnotationToAttribute(GraphQLite\\ExtendType::class),\n            new AnnotationToAttribute(GraphQLite\\Input::class),\n            new AnnotationToAttribute(GraphQLite\\Field::class),\n            new AnnotationToAttribute(GraphQLite\\SourceField::class),\n            new AnnotationToAttribute(GraphQLite\\MagicField::class),\n            new AnnotationToAttribute(GraphQLite\\Logged::class),\n            new AnnotationToAttribute(GraphQLite\\Right::class),\n            new AnnotationToAttribute(GraphQLite\\FailWith::class),\n            new AnnotationToAttribute(GraphQLite\\HideIfUnauthorized::class),\n            new AnnotationToAttribute(GraphQLite\\InjectUser::class),\n            new AnnotationToAttribute(GraphQLite\\Security::class),\n            new AnnotationToAttribute(GraphQLite\\Factory::class),\n            new AnnotationToAttribute(GraphQLite\\UseInputType::class),\n            new AnnotationToAttribute(GraphQLite\\Decorate::class),\n            new AnnotationToAttribute(GraphQLite\\Autowire::class),\n            new AnnotationToAttribute(GraphQLite\\HideParameter::class),\n            new AnnotationToAttribute(GraphQLite\\EnumType::class),\n        ]);\n};\n")))}d.isMDXComponent=!0}}]);