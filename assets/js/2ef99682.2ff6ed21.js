"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[9092],{27486:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>u,contentTitle:()=>o,default:()=>d,frontMatter:()=>s,metadata:()=>i,toc:()=>l});var n=a(58168),p=(a(96540),a(15680));a(67443);const s={id:"custom-output-types",title:"Custom output types",sidebar_label:"Custom output types",original_id:"custom-output-types"},o=void 0,i={unversionedId:"custom-output-types",id:"version-3.0/custom-output-types",title:"Custom output types",description:"In some special cases, you want to override the GraphQL return type that is attributed by default by GraphQLite.",source:"@site/versioned_docs/version-3.0/custom_output_types.md",sourceDirName:".",slug:"/custom-output-types",permalink:"/docs/3.0/custom-output-types",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/custom_output_types.md",tags:[],version:"3.0",lastUpdatedBy:"Oleksandr Prypkhan",lastUpdatedAt:1711930569,formattedLastUpdatedAt:"Apr 1, 2024",frontMatter:{id:"custom-output-types",title:"Custom output types",sidebar_label:"Custom output types",original_id:"custom-output-types"},sidebar:"version-3.0/docs",previous:{title:"Pagination",permalink:"/docs/3.0/pagination"},next:{title:"Troubleshooting",permalink:"/docs/3.0/troubleshooting"}},u={},l=[{value:"Usage",id:"usage",level:2},{value:"Registering a custom output type (advanced)",id:"registering-a-custom-output-type-advanced",level:2},{value:"Symfony users",id:"symfony-users",level:3},{value:"Other frameworks",id:"other-frameworks",level:3}],r={toc:l},y="wrapper";function d(e){let{components:t,...a}=e;return(0,p.yg)(y,(0,n.A)({},r,a,{components:t,mdxType:"MDXLayout"}),(0,p.yg)("p",null,"In some special cases, you want to override the GraphQL return type that is attributed by default by GraphQLite."),(0,p.yg)("p",null,"For instance:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type(class=Product::class)\n */\nclass ProductType\n{\n    /**\n     * @Field(name="id")\n     */\n    public function getId(Product $source): string\n    {\n        return $source->getId();\n    }\n}\n')),(0,p.yg)("p",null,"In the example above, GraphQLite will generate a GraphQL schema with a field ",(0,p.yg)("inlineCode",{parentName:"p"},"id")," of type ",(0,p.yg)("inlineCode",{parentName:"p"},"string"),":"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-graphql"},"type Product {\n    id: String!\n}\n")),(0,p.yg)("p",null,"GraphQL comes with an ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," scalar type. But PHP has no such type. So GraphQLite does not know when a variable\nis an ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," or not."),(0,p.yg)("p",null,"You can help GraphQLite by manually specifying the output type to use:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'    /**\n     * @Field(name="id", outputType="ID!")\n     */\n')),(0,p.yg)("h2",{id:"usage"},"Usage"),(0,p.yg)("p",null,"The ",(0,p.yg)("inlineCode",{parentName:"p"},"outputType")," attribute will map the return value of the method to the output type passed in parameter."),(0,p.yg)("p",null,"You can use the ",(0,p.yg)("inlineCode",{parentName:"p"},"outputType")," attribute in the following annotations:"),(0,p.yg)("ul",null,(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"@Query")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"@Mutation")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"@Field")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"@SourceField"))),(0,p.yg)("h2",{id:"registering-a-custom-output-type-advanced"},"Registering a custom output type (advanced)"),(0,p.yg)("p",null,"In order to create a custom output type, you need to:"),(0,p.yg)("ol",null,(0,p.yg)("li",{parentName:"ol"},"Design a class that extends ",(0,p.yg)("inlineCode",{parentName:"li"},"GraphQL\\Type\\Definition\\ObjectType"),"."),(0,p.yg)("li",{parentName:"ol"},"Register this class in the GraphQL schema.")),(0,p.yg)("p",null,"You'll find more details on the ",(0,p.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/type-system/object-types/"},"Webonyx documentation"),"."),(0,p.yg)("hr",null),(0,p.yg)("p",null,"In order to find existing types, the schema is using ",(0,p.yg)("em",{parentName:"p"},"type mappers")," (classes implementing the ",(0,p.yg)("inlineCode",{parentName:"p"},"TypeMapperInterface")," interface)."),(0,p.yg)("p",null,"You need to make sure that one of these type mappers can return an instance of your type. The way you do this will depend on the framework\nyou use."),(0,p.yg)("h3",{id:"symfony-users"},"Symfony users"),(0,p.yg)("p",null,"Any class extending ",(0,p.yg)("inlineCode",{parentName:"p"},"GraphQL\\Type\\Definition\\ObjectType")," (and available in the container) will be automatically detected\nby Symfony and added to the schema."),(0,p.yg)("p",null,"If you want to automatically map the output type to a given PHP class, you will have to explicitly declare the output type\nas a service and use the ",(0,p.yg)("inlineCode",{parentName:"p"},"graphql.output_type")," tag:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-yaml"},"# config/services.yaml\nservices:\n    App\\MyOutputType:\n        tags:\n            - { name: 'graphql.output_type', class: 'App\\MyPhpClass' }\n")),(0,p.yg)("h3",{id:"other-frameworks"},"Other frameworks"),(0,p.yg)("p",null,"The easiest way is to use a ",(0,p.yg)("inlineCode",{parentName:"p"},"StaticTypeMapper"),". This class is used to register custom output types."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"// Sample code:\n$staticTypeMapper = new StaticTypeMapper();\n\n// Let's register a type that maps by default to the \"MyClass\" PHP class\n$staticTypeMapper->setTypes([\n    MyClass::class => new MyCustomOutputType()\n]);\n\n// If you don't want your output type to map to any PHP class by default, use:\n$staticTypeMapper->setNotMappedTypes([\n    new MyCustomOutputType()\n]);\n\n")),(0,p.yg)("p",null,"The ",(0,p.yg)("inlineCode",{parentName:"p"},"StaticTypeMapper")," instance MUST be registered in your container and linked to a ",(0,p.yg)("inlineCode",{parentName:"p"},"CompositeTypeMapper"),"\nthat will aggregate all the type mappers of the application."))}d.isMDXComponent=!0}}]);