"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[3189],{918:(e,n,t)=>{t.r(n),t.d(n,{assets:()=>u,contentTitle:()=>p,default:()=>h,frontMatter:()=>r,metadata:()=>s,toc:()=>d});var a=t(7462),i=(t(7294),t(3905)),o=(t(1839),t(5488)),l=t(5162);const r={id:"external-type-declaration",title:"External type declaration",sidebar_label:"External type declaration"},p=void 0,s={unversionedId:"external-type-declaration",id:"version-6.0/external-type-declaration",title:"External type declaration",description:"In some cases, you cannot or do not want to put an annotation on a domain class.",source:"@site/versioned_docs/version-6.0/external-type-declaration.mdx",sourceDirName:".",slug:"/external-type-declaration",permalink:"/docs/6.0/external-type-declaration",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-6.0/external-type-declaration.mdx",tags:[],version:"6.0",lastUpdatedBy:"Snyk bot",lastUpdatedAt:1672898619,formattedLastUpdatedAt:"1/5/2023",frontMatter:{id:"external-type-declaration",title:"External type declaration",sidebar_label:"External type declaration"},sidebar:"docs",previous:{title:"Extending a type",permalink:"/docs/6.0/extend-type"},next:{title:"Input types",permalink:"/docs/6.0/input-types"}},u={},d=[{value:"<code>@Type</code> annotation with the <code>class</code> attribute",id:"type-annotation-with-the-class-attribute",level:2},{value:"<code>@SourceField</code> annotation",id:"sourcefield-annotation",level:2},{value:"<code>@MagicField</code> annotation",id:"magicfield-annotation",level:2},{value:"Authentication and authorization",id:"authentication-and-authorization",level:3},{value:"Declaring fields dynamically (without annotations)",id:"declaring-fields-dynamically-without-annotations",level:2}],c={toc:d};function h(e){let{components:n,...t}=e;return(0,i.kt)("wrapper",(0,a.Z)({},c,t,{components:n,mdxType:"MDXLayout"}),(0,i.kt)("p",null,"In some cases, you cannot or do not want to put an annotation on a domain class."),(0,i.kt)("p",null,"For instance:"),(0,i.kt)("ul",null,(0,i.kt)("li",{parentName:"ul"},"The class you want to annotate is part of a third party library and you cannot modify it"),(0,i.kt)("li",{parentName:"ul"},"You are doing domain-driven design and don't want to clutter your domain object with annotations from the view layer"),(0,i.kt)("li",{parentName:"ul"},"etc.")),(0,i.kt)("h2",{id:"type-annotation-with-the-class-attribute"},(0,i.kt)("inlineCode",{parentName:"h2"},"@Type")," annotation with the ",(0,i.kt)("inlineCode",{parentName:"h2"},"class")," attribute"),(0,i.kt)("p",null,"GraphQLite allows you to use a ",(0,i.kt)("em",{parentName:"p"},"proxy")," class thanks to the ",(0,i.kt)("inlineCode",{parentName:"p"},"@Type")," annotation with the ",(0,i.kt)("inlineCode",{parentName:"p"},"class")," attribute:"),(0,i.kt)(o.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.kt)(l.Z,{value:"php8",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},"namespace App\\Types;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse App\\Entities\\Product;\n\n#[Type(class: Product::class)]\nclass ProductType\n{\n    #[Field]\n    public function getId(Product $product): string\n    {\n        return $product->getId();\n    }\n}\n"))),(0,i.kt)(l.Z,{value:"php7",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},"namespace App\\Types;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse App\\Entities\\Product;\n\n/**\n * @Type(class=Product::class)\n */\nclass ProductType\n{\n    /**\n     * @Field()\n     */\n    public function getId(Product $product): string\n    {\n        return $product->getId();\n    }\n}\n")))),(0,i.kt)("p",null,"The ",(0,i.kt)("inlineCode",{parentName:"p"},"ProductType")," class must be in the ",(0,i.kt)("em",{parentName:"p"},"types")," namespace. You configured this namespace when you installed GraphQLite."),(0,i.kt)("p",null,"The ",(0,i.kt)("inlineCode",{parentName:"p"},"ProductType")," class is actually a ",(0,i.kt)("strong",{parentName:"p"},"service"),". You can therefore inject dependencies in it."),(0,i.kt)("div",{class:"alert alert--warning"},(0,i.kt)("strong",null,"Heads up!")," The ",(0,i.kt)("code",null,"ProductType")," class must exist in the container of your application and the container identifier MUST be the fully qualified class name.",(0,i.kt)("br",null),(0,i.kt)("br",null),"If you are using the Symfony bundle (or a framework with autowiring like Laravel), this is usually not an issue as the container will automatically create the controller entry if you do not explicitly declare it."),(0,i.kt)("p",null,"In methods with a ",(0,i.kt)("inlineCode",{parentName:"p"},"@Field")," annotation, the first parameter is the ",(0,i.kt)("em",{parentName:"p"},"resolved object")," we are working on. Any additional parameters are used as arguments."),(0,i.kt)("h2",{id:"sourcefield-annotation"},(0,i.kt)("inlineCode",{parentName:"h2"},"@SourceField")," annotation"),(0,i.kt)("p",null,"If you don't want to rewrite all ",(0,i.kt)("em",{parentName:"p"},"getters")," of your base class, you may use the ",(0,i.kt)("inlineCode",{parentName:"p"},"@SourceField")," annotation:"),(0,i.kt)(o.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.kt)(l.Z,{value:"php8",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse App\\Entities\\Product;\n\n#[Type(class: Product::class)]\n#[SourceField(name: "name")]\n#[SourceField(name: "price")]\nclass ProductType\n{\n}\n'))),(0,i.kt)(l.Z,{value:"php7",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse App\\Entities\\Product;\n\n/**\n * @Type(class=Product::class)\n * @SourceField(name="name")\n * @SourceField(name="price")\n */\nclass ProductType\n{\n}\n')))),(0,i.kt)("p",null,"By doing so, you let GraphQLite know that the type exposes the ",(0,i.kt)("inlineCode",{parentName:"p"},"getName")," method of the underlying ",(0,i.kt)("inlineCode",{parentName:"p"},"Product")," object."),(0,i.kt)("p",null,"Internally, GraphQLite will look for methods named ",(0,i.kt)("inlineCode",{parentName:"p"},"name()"),", ",(0,i.kt)("inlineCode",{parentName:"p"},"getName()")," and ",(0,i.kt)("inlineCode",{parentName:"p"},"isName()"),").\nYou can set different name to look for with ",(0,i.kt)("inlineCode",{parentName:"p"},"sourceName")," attribute."),(0,i.kt)("h2",{id:"magicfield-annotation"},(0,i.kt)("inlineCode",{parentName:"h2"},"@MagicField")," annotation"),(0,i.kt)("p",null,"If your object has no getters, but instead uses magic properties (using the magic ",(0,i.kt)("inlineCode",{parentName:"p"},"__get")," method), you should use the ",(0,i.kt)("inlineCode",{parentName:"p"},"@MagicField")," annotation:"),(0,i.kt)(o.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.kt)(l.Z,{value:"php8",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse App\\Entities\\Product;\n\n#[Type]\n#[MagicField(name: "name", outputType: "String!")]\n#[MagicField(name: "price", outputType: "Float")]\nclass ProductType\n{\n    public function __get(string $property) {\n        // return some magic property\n    }\n}\n'))),(0,i.kt)(l.Z,{value:"php7",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse App\\Entities\\Product;\n\n/**\n * @Type()\n * @MagicField(name="name", outputType="String!")\n * @MagicField(name="price", outputType="Float")\n */\nclass ProductType\n{\n    public function __get(string $property) {\n        // return some magic property\n    }\n}\n')))),(0,i.kt)("p",null,'By doing so, you let GraphQLite know that the type exposes "name" and the "price" magic properties of the underlying ',(0,i.kt)("inlineCode",{parentName:"p"},"Product")," object.\nYou can set different name to look for with ",(0,i.kt)("inlineCode",{parentName:"p"},"sourceName")," attribute."),(0,i.kt)("p",null,"This is particularly useful in frameworks like Laravel, where Eloquent is making a very wide use of such properties."),(0,i.kt)("p",null,"Please note that GraphQLite has no way to know the type of a magic property. Therefore, you have specify the GraphQL type\nof each property manually."),(0,i.kt)("h3",{id:"authentication-and-authorization"},"Authentication and authorization"),(0,i.kt)("p",null,'You may also check for logged users or users with a specific right using the "annotations" property.'),(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Logged;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Right;\nuse TheCodingMachine\\GraphQLite\\Annotations\\FailWith;\nuse App\\Entities\\Product;\n\n/**\n * @Type(class=Product::class)\n * @SourceField(name="name")\n * @SourceField(name="price", annotations={@Logged, @Right(name="CAN_ACCESS_Price", @FailWith(null)}))\n */\nclass ProductType extends AbstractAnnotatedObjectType\n{\n}\n')),(0,i.kt)("p",null,"Any annotations described in the ",(0,i.kt)("a",{parentName:"p",href:"/docs/6.0/authentication-authorization"},"Authentication and authorization page"),", or any annotation this is actually a ",(0,i.kt)("a",{parentName:"p",href:"/docs/6.0/field-middlewares"},'"field middleware"')," can be used in the ",(0,i.kt)("inlineCode",{parentName:"p"},"@SourceField"),' "annotations" attribute.'),(0,i.kt)("div",{class:"alert alert--warning"},(0,i.kt)("strong",null,"Heads up!"),' The "annotation" attribute in @SourceField and @MagicField is only available as a ',(0,i.kt)("strong",null,"Doctrine annotations"),". You cannot use it in PHP 8 attributes (because PHP 8 attributes cannot be nested)"),(0,i.kt)("h2",{id:"declaring-fields-dynamically-without-annotations"},"Declaring fields dynamically (without annotations)"),(0,i.kt)("p",null,"In some very particular cases, you might not know exactly the list of ",(0,i.kt)("inlineCode",{parentName:"p"},"@SourceField")," annotations at development time.\nIf you need to decide the list of ",(0,i.kt)("inlineCode",{parentName:"p"},"@SourceField")," at runtime, you can implement the ",(0,i.kt)("inlineCode",{parentName:"p"},"FromSourceFieldsInterface"),":"),(0,i.kt)(o.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.kt)(l.Z,{value:"php8",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\FromSourceFieldsInterface;\n\n#[Type(class: Product::class)]\nclass ProductType implements FromSourceFieldsInterface\n{\n    /**\n     * Dynamically returns the array of source fields\n     * to be fetched from the original object.\n     *\n     * @return SourceFieldInterface[]\n     */\n    public function getSourceFields(): array\n    {\n        // You may want to enable fields conditionally based on feature flags...\n        if (ENABLE_STATUS_GLOBALLY) {\n            return [\n                new SourceField(['name'=>'status', 'logged'=>true]),\n            ];\n        } else {\n            return [];\n        }\n    }\n}\n"))),(0,i.kt)(l.Z,{value:"php7",mdxType:"TabItem"},(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\FromSourceFieldsInterface;\n\n/**\n * @Type(class=Product::class)\n */\nclass ProductType implements FromSourceFieldsInterface\n{\n    /**\n     * Dynamically returns the array of source fields\n     * to be fetched from the original object.\n     *\n     * @return SourceFieldInterface[]\n     */\n    public function getSourceFields(): array\n    {\n        // You may want to enable fields conditionally based on feature flags...\n        if (ENABLE_STATUS_GLOBALLY) {\n            return [\n                new SourceField(['name'=>'status', 'logged'=>true]),\n            ];\n        } else {\n            return [];\n        }\n    }\n}\n")))))}h.isMDXComponent=!0}}]);