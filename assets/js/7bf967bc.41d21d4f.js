"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5619],{6064:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>d,contentTitle:()=>s,default:()=>m,frontMatter:()=>i,metadata:()=>o,toc:()=>r});var a=n(58168),l=(n(96540),n(15680)),p=(n(67443),n(11470)),u=n(19365);const i={id:"multiple-output-types",title:"Mapping multiple output types for the same class",sidebar_label:"Class with multiple output types"},s=void 0,o={unversionedId:"multiple-output-types",id:"multiple-output-types",title:"Mapping multiple output types for the same class",description:"Available in GraphQLite 4.0+",source:"@site/docs/multiple-output-types.mdx",sourceDirName:".",slug:"/multiple-output-types",permalink:"/docs/next/multiple-output-types",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/multiple-output-types.mdx",tags:[],version:"current",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1712955610,formattedLastUpdatedAt:"Apr 12, 2024",frontMatter:{id:"multiple-output-types",title:"Mapping multiple output types for the same class",sidebar_label:"Class with multiple output types"},sidebar:"docs",previous:{title:"Extending an input type",permalink:"/docs/next/extend-input-type"},next:{title:"Symfony specific features",permalink:"/docs/next/symfony-bundle-advanced"}},d={},r=[{value:"Example",id:"example",level:2},{value:"Extending a non-default type",id:"extending-a-non-default-type",level:2}],y={toc:r},c="wrapper";function m(e){let{components:t,...n}=e;return(0,l.yg)(c,(0,a.A)({},y,n,{components:t,mdxType:"MDXLayout"}),(0,l.yg)("small",null,"Available in GraphQLite 4.0+"),(0,l.yg)("p",null,"In most cases, you have one PHP class and you want to map it to one GraphQL output type."),(0,l.yg)("p",null,"But in very specific cases, you may want to use different GraphQL output type for the same class.\nFor instance, depending on the context, you might want to prevent the user from accessing some fields of your object."),(0,l.yg)("p",null,'To do so, you need to create 2 output types for the same PHP class. You typically do this using the "default" attribute of the ',(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," annotation."),(0,l.yg)("h2",{id:"example"},"Example"),(0,l.yg)("p",null,"Here is an example. Say we are manipulating products. When I query a ",(0,l.yg)("inlineCode",{parentName:"p"},"Product")," details, I want to have access to all fields.\nBut for some reason, I don't want to expose the price field of a product if I query the list of all products."),(0,l.yg)(p.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(u.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass Product\n{\n    // ...\n\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    #[Field]\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n"))),(0,l.yg)(u.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    /**\n     * @Field()\n     */\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n")))),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"Product"),' class is declaring a classic GraphQL output type named "Product".'),(0,l.yg)(p.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(u.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'#[Type(class: Product::class, name: "LimitedProduct", default: false)]\n#[SourceField(name: "name")]\nclass LimitedProductType\n{\n    // ...\n}\n'))),(0,l.yg)(u.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type(class=Product::class, name="LimitedProduct", default=false)\n * @SourceField(name="name")\n */\nclass LimitedProductType\n{\n    // ...\n}\n')))),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"LimitedProductType")," also declares an ",(0,l.yg)("a",{parentName:"p",href:"/docs/next/external-type-declaration"},'"external" type')," mapping the ",(0,l.yg)("inlineCode",{parentName:"p"},"Product")," class.\nBut pay special attention to the ",(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," annotation."),(0,l.yg)("p",null,"First of all, we specify ",(0,l.yg)("inlineCode",{parentName:"p"},'name="LimitedProduct"'),'. This is useful to avoid having colliding names with the "Product" GraphQL output type\nthat is already declared.'),(0,l.yg)("p",null,"Then, we specify ",(0,l.yg)("inlineCode",{parentName:"p"},"default=false"),". This means that by default, the ",(0,l.yg)("inlineCode",{parentName:"p"},"Product")," class should not be mapped to the ",(0,l.yg)("inlineCode",{parentName:"p"},"LimitedProductType"),".\nThis type will only be used when we explicitly request it."),(0,l.yg)("p",null,"Finally, we can write our requests:"),(0,l.yg)(p.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(u.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'class ProductController\n{\n    /**\n     * This field will use the default type.\n     */\n    #[Field]\n    public function getProduct(int $id): Product { /* ... */ }\n\n    /**\n     * Because we use the "outputType" attribute, this field will use the other type.\n     *\n     * @return Product[]\n     */\n    #[Field(outputType: "[LimitedProduct!]!")]\n    public function getProducts(): array { /* ... */ }\n}\n'))),(0,l.yg)(u.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'class ProductController\n{\n    /**\n     * This field will use the default type.\n     *\n     * @Field\n     */\n    public function getProduct(int $id): Product { /* ... */ }\n\n    /**\n     * Because we use the "outputType" attribute, this field will use the other type.\n     *\n     * @Field(outputType="[LimitedProduct!]!")\n     * @return Product[]\n     */\n    public function getProducts(): array { /* ... */ }\n}\n')))),(0,l.yg)("p",null,'Notice how the "outputType" attribute is used in the ',(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," annotation to force the output type."),(0,l.yg)("p",null,"Is a result, when the end user calls the ",(0,l.yg)("inlineCode",{parentName:"p"},"product")," query, we will have the possibility to fetch the ",(0,l.yg)("inlineCode",{parentName:"p"},"name")," and ",(0,l.yg)("inlineCode",{parentName:"p"},"price")," fields,\nbut if he calls the ",(0,l.yg)("inlineCode",{parentName:"p"},"products")," query, each product in the list will have a ",(0,l.yg)("inlineCode",{parentName:"p"},"name")," field but no ",(0,l.yg)("inlineCode",{parentName:"p"},"price")," field. We managed\nto successfully expose a different set of fields based on the query context."),(0,l.yg)("h2",{id:"extending-a-non-default-type"},"Extending a non-default type"),(0,l.yg)("p",null,"If you want to extend a type using the ",(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType")," annotation and if this type is declared as non-default,\nyou need to target the type by name instead of by class."),(0,l.yg)("p",null,"So instead of writing:"),(0,l.yg)(p.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(u.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"#[ExtendType(class: Product::class)]\n"))),(0,l.yg)(u.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @ExtendType(class=Product::class)\n */\n")))),(0,l.yg)("p",null,"you will write:"),(0,l.yg)(p.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(u.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'#[ExtendType(name: "LimitedProduct")]\n'))),(0,l.yg)(u.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @ExtendType(name="LimitedProduct")\n */\n')))),(0,l.yg)("p",null,'Notice how we use the "name" attribute instead of the "class" attribute in the ',(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType")," annotation."))}m.isMDXComponent=!0}}]);