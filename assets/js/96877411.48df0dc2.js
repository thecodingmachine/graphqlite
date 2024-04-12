"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5526],{80863:(e,n,t)=>{t.r(n),t.d(n,{assets:()=>d,contentTitle:()=>s,default:()=>y,frontMatter:()=>p,metadata:()=>o,toc:()=>c});var a=t(58168),i=(t(96540),t(15680)),l=(t(67443),t(11470)),r=t(19365);const p={id:"extend-type",title:"Extending a type",sidebar_label:"Extending a type"},s=void 0,o={unversionedId:"extend-type",id:"extend-type",title:"Extending a type",description:"Fields exposed in a GraphQL type do not need to be all part of the same class.",source:"@site/docs/extend-type.mdx",sourceDirName:".",slug:"/extend-type",permalink:"/docs/next/extend-type",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/extend-type.mdx",tags:[],version:"current",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1712955610,formattedLastUpdatedAt:"Apr 12, 2024",frontMatter:{id:"extend-type",title:"Extending a type",sidebar_label:"Extending a type"},sidebar:"docs",previous:{title:"Autowiring services",permalink:"/docs/next/autowiring"},next:{title:"External type declaration",permalink:"/docs/next/external-type-declaration"}},d={},c=[],u={toc:c},g="wrapper";function y(e){let{components:n,...t}=e;return(0,i.yg)(g,(0,a.A)({},u,t,{components:n,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"Fields exposed in a GraphQL type do not need to be all part of the same class."),(0,i.yg)("p",null,"Use the ",(0,i.yg)("inlineCode",{parentName:"p"},"@ExtendType")," annotation to add additional fields to a type that is already declared."),(0,i.yg)("div",{class:"alert alert--info"},"Extending a type has nothing to do with type inheritance. If you are looking for a way to expose a class and its children classes, have a look at the ",(0,i.yg)("a",{href:"inheritance-interfaces"},"Inheritance")," section"),(0,i.yg)("p",null,"Let's assume you have a ",(0,i.yg)("inlineCode",{parentName:"p"},"Product")," class. In order to get the name of a product, there is no ",(0,i.yg)("inlineCode",{parentName:"p"},"getName()")," method in\nthe product because the name needs to be translated in the correct language. You have a ",(0,i.yg)("inlineCode",{parentName:"p"},"TranslationService")," to do that."),(0,i.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n#[Type]\nclass Product\n{\n    // ...\n\n    #[Field]\n    public function getId(): string\n    {\n        return $this->id;\n    }\n\n    #[Field]\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n"))),(0,i.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getId(): string\n    {\n        return $this->id;\n    }\n\n    /**\n     * @Field()\n     */\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n")))),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"// You need to use a service to get the name of the product in the correct language.\n$name = $translationService->getProductName($productId, $language);\n")),(0,i.yg)("p",null,"Using ",(0,i.yg)("inlineCode",{parentName:"p"},"@ExtendType"),", you can add an additional ",(0,i.yg)("inlineCode",{parentName:"p"},"name")," field to your product:"),(0,i.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Types;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\ExtendType;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse App\\Entities\\Product;\n\n#[ExtendType(class: Product::class)]\nclass ProductType\n{\n    private $translationService;\n\n    public function __construct(TranslationServiceInterface $translationService)\n    {\n        $this->translationService = $translationService;\n    }\n\n    #[Field]\n    public function getName(Product $product, string $language): string\n    {\n        return $this->translationService->getProductName($product->getId(), $language);\n    }\n}\n"))),(0,i.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Types;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\ExtendType;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse App\\Entities\\Product;\n\n/**\n * @ExtendType(class=Product::class)\n */\nclass ProductType\n{\n    private $translationService;\n\n    public function __construct(TranslationServiceInterface $translationService)\n    {\n        $this->translationService = $translationService;\n    }\n\n    /**\n     * @Field()\n     */\n    public function getName(Product $product, string $language): string\n    {\n        return $this->translationService->getProductName($product->getId(), $language);\n    }\n}\n")))),(0,i.yg)("p",null,"Let's break this sample:"),(0,i.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"#[ExtendType(class: Product::class)]\n"))),(0,i.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @ExtendType(class=Product::class)\n */\n")))),(0,i.yg)("p",null,"With the ",(0,i.yg)("inlineCode",{parentName:"p"},"@ExtendType")," annotation, we tell GraphQLite that we want to add fields in the GraphQL type mapped to\nthe ",(0,i.yg)("inlineCode",{parentName:"p"},"Product")," PHP class."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class ProductType\n{\n    private $translationService;\n\n    public function __construct(TranslationServiceInterface $translationService)\n    {\n        $this->translationService = $translationService;\n    }\n\n    // ...\n}\n")),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"ProductType")," class must be in the types namespace. You configured this namespace when you installed GraphQLite."),(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"ProductType")," class is actually a ",(0,i.yg)("strong",{parentName:"li"},"service"),". You can therefore inject dependencies in it (like the ",(0,i.yg)("inlineCode",{parentName:"li"},"$translationService")," in this example)")),(0,i.yg)("div",{class:"alert alert--warning"},(0,i.yg)("strong",null,"Heads up!")," The ",(0,i.yg)("code",null,"ProductType")," class must exist in the container of your application and the container identifier MUST be the fully qualified class name.",(0,i.yg)("br",null),(0,i.yg)("br",null),"If you are using the Symfony bundle (or a framework with autowiring like Laravel), this is usually not an issue as the container will automatically create the controller entry if you do not explicitly declare it."),(0,i.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"#[Field]\npublic function getName(Product $product, string $language): string\n{\n    return $this->translationService->getProductName($product->getId(), $language);\n}\n"))),(0,i.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Field()\n */\npublic function getName(Product $product, string $language): string\n{\n    return $this->translationService->getProductName($product->getId(), $language);\n}\n")))),(0,i.yg)("p",null,"The ",(0,i.yg)("inlineCode",{parentName:"p"},"@Field"),' annotation is used to add the "name" field to the ',(0,i.yg)("inlineCode",{parentName:"p"},"Product")," type."),(0,i.yg)("p",null,'Take a close look at the signature. The first parameter is the "resolved object" we are working on.\nAny additional parameters are used as arguments.'),(0,i.yg)("p",null,'Using the "',(0,i.yg)("a",{parentName:"p",href:"https://graphql.org/learn/schema/#type-language"},"Type language"),'" notation, we defined a type extension for\nthe GraphQL "Product" type:'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-graphql"},"Extend type Product {\n    name(language: !String): String!\n}\n")),(0,i.yg)("div",{class:"alert alert--success"},"Type extension is a very powerful tool. Use it to add fields that needs to be computed from services not available in the entity."))}y.isMDXComponent=!0}}]);