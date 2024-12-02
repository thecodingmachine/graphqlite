"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5547],{86017:(t,e,n)=>{n.r(e),n.d(e,{assets:()=>l,contentTitle:()=>r,default:()=>c,frontMatter:()=>o,metadata:()=>s,toc:()=>p});var a=n(58168),i=(n(96540),n(15680));n(67443);const o={id:"doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",sidebar_label:"Annotations VS Attributes"},r=void 0,s={unversionedId:"doctrine-annotations-attributes",id:"version-4.0/doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",description:"GraphQLite is heavily relying on the concept of annotations (also called attributes in PHP 8+).",source:"@site/versioned_docs/version-4.0/doctrine_annotations_attributes.md",sourceDirName:".",slug:"/doctrine-annotations-attributes",permalink:"/docs/4.0/doctrine-annotations-attributes",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.0/doctrine_annotations_attributes.md",tags:[],version:"4.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1733174531,formattedLastUpdatedAt:"Dec 2, 2024",frontMatter:{id:"doctrine-annotations-attributes",title:"Doctrine annotations VS PHP8 attributes",sidebar_label:"Annotations VS Attributes"}},l={},p=[{value:"Doctrine annotations",id:"doctrine-annotations",level:2},{value:"PHP 8 attributes",id:"php-8-attributes",level:2}],u={toc:p},d="wrapper";function c(t){let{components:e,...n}=t;return(0,i.yg)(d,(0,a.A)({},u,n,{components:e,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"GraphQLite is heavily relying on the concept of annotations (also called attributes in PHP 8+)."),(0,i.yg)("h2",{id:"doctrine-annotations"},"Doctrine annotations"),(0,i.yg)("div",{class:"alert alert--warning"},(0,i.yg)("strong",null,"Deprecated!")," Doctrine annotations are deprecated in favor of native PHP 8 attributes. Support will be dropped in GraphQLite 5.0"),(0,i.yg)("p",null,'Historically, attributes were not available in PHP and PHP developers had to "trick" PHP to get annotation support.\nThis was the purpose of the ',(0,i.yg)("a",{parentName:"p",href:"https://www.doctrine-project.org/projects/doctrine-annotations/en/latest/index.html"},"doctrine/annotation")," library."),(0,i.yg)("p",null,"Using Doctrine annotations, you write annotations in your docblocks:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n/**\n * @Type\n */\nclass MyType\n{\n}\n")),(0,i.yg)("p",null,"Please note that:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"The annotation is added in a ",(0,i.yg)("strong",{parentName:"li"},"docblock"),' (a comment starting with "',(0,i.yg)("inlineCode",{parentName:"li"},"/**"),'")'),(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"Type")," part is actually a class. It must be declared in the ",(0,i.yg)("inlineCode",{parentName:"li"},"use")," statements at the top of your file.")),(0,i.yg)("div",{class:"alert alert--info"},(0,i.yg)("strong",null,"Heads up!"),"Some IDEs provide support for Doctrine annotations:",(0,i.yg)("ul",null,(0,i.yg)("li",null,"PhpStorm via the ",(0,i.yg)("a",{href:"https://plugins.jetbrains.com/plugin/7320-php-annotations"},"PHP Annotations Plugin")),(0,i.yg)("li",null,"Eclipse via the ",(0,i.yg)("a",{href:"https://marketplace.eclipse.org/content/symfony-plugin"},"Symfony 2 Plugin")),(0,i.yg)("li",null,"Netbeans has native support")),(0,i.yg)("p",null,"We strongly recommend using an IDE that has Doctrine annotations support.")),(0,i.yg)("h2",{id:"php-8-attributes"},"PHP 8 attributes"),(0,i.yg)("p",null,'Starting with PHP 8, PHP got native annotations support. They are actually called "attributes" in the PHP world.'),(0,i.yg)("p",null,"The same code can be written this way:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n#[Type]\nclass MyType\n{\n}\n")),(0,i.yg)("p",null,"GraphQLite v4.1+ has support for PHP 8 attributes."),(0,i.yg)("p",null,"The Doctrine annotation class and the PHP 8 attribute class is ",(0,i.yg)("strong",{parentName:"p"},"the same")," (so you will be using the same ",(0,i.yg)("inlineCode",{parentName:"p"},"use")," statement at the top of your file)."),(0,i.yg)("p",null,"They support the same attributes too."),(0,i.yg)("p",null,"A few notable differences:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"PHP 8 attributes do not support nested attributes (unlike Doctrine annotations). This means there is no equivalent to the ",(0,i.yg)("inlineCode",{parentName:"li"},"annotations")," attribute of ",(0,i.yg)("inlineCode",{parentName:"li"},"@MagicField")," and ",(0,i.yg)("inlineCode",{parentName:"li"},"@SourceField"),"."),(0,i.yg)("li",{parentName:"ul"},'PHP 8 attributes can be written at the parameter level. Any attribute targeting a "parameter" must be written at the parameter level.')),(0,i.yg)("p",null,"Let's take an example with the ",(0,i.yg)("a",{parentName:"p",href:"/docs/4.0/autowiring"},(0,i.yg)("inlineCode",{parentName:"a"},"#Autowire")," attribute"),":"),(0,i.yg)("p",null,(0,i.yg)("strong",{parentName:"p"},"PHP 7+")),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},'/**\n * @Field\n * @Autowire(for="$productRepository")\n */\npublic function getProduct(ProductRepository $productRepository) : Product {\n    //...\n}\n')),(0,i.yg)("p",null,(0,i.yg)("strong",{parentName:"p"},"PHP 8")),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},"#[Field]\npublic function getProduct(#[Autowire] ProductRepository $productRepository) : Product {\n    //...\n}\n")))}c.isMDXComponent=!0}}]);