"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[1985],{1273:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>l,contentTitle:()=>o,default:()=>c,frontMatter:()=>r,metadata:()=>y,toc:()=>i});var n=a(58168),p=(a(96540),a(15680));a(67443);const r={id:"custom-types",title:"Custom types",sidebar_label:"Custom types"},o=void 0,y={unversionedId:"custom-types",id:"custom-types",title:"Custom types",description:"In some special cases, you want to override the GraphQL return type that is attributed by default by GraphQLite.",source:"@site/docs/custom-types.mdx",sourceDirName:".",slug:"/custom-types",permalink:"/docs/next/custom-types",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/custom-types.mdx",tags:[],version:"current",lastUpdatedBy:"sudevva",lastUpdatedAt:1728787444,formattedLastUpdatedAt:"Oct 13, 2024",frontMatter:{id:"custom-types",title:"Custom types",sidebar_label:"Custom types"},sidebar:"docs",previous:{title:"Pagination",permalink:"/docs/next/pagination"},next:{title:"Custom attributes",permalink:"/docs/next/field-middlewares"}},l={},i=[{value:"Usage",id:"usage",level:2},{value:"Registering a custom output type (advanced)",id:"registering-a-custom-output-type-advanced",level:2},{value:"Symfony users",id:"symfony-users",level:3},{value:"Other frameworks",id:"other-frameworks",level:3},{value:"Registering a custom scalar type (advanced)",id:"registering-a-custom-scalar-type-advanced",level:2}],s={toc:i},u="wrapper";function c(e){let{components:t,...a}=e;return(0,p.yg)(u,(0,n.A)({},s,a,{components:t,mdxType:"MDXLayout"}),(0,p.yg)("p",null,"In some special cases, you want to override the GraphQL return type that is attributed by default by GraphQLite."),(0,p.yg)("p",null,"For instance:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"#[Type(class: Product::class)]\nclass ProductType\n{\n    #[Field]\n    public function getId(Product $source): string\n    {\n        return $source->getId();\n    }\n}\n")),(0,p.yg)("p",null,"In the example above, GraphQLite will generate a GraphQL schema with a field ",(0,p.yg)("inlineCode",{parentName:"p"},"id")," of type ",(0,p.yg)("inlineCode",{parentName:"p"},"string"),":"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-graphql"},"type Product {\n    id: String!\n}\n")),(0,p.yg)("p",null,"GraphQL comes with an ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," scalar type. But PHP has no such type. So GraphQLite does not know when a variable\nis an ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," or not."),(0,p.yg)("p",null,"You can help GraphQLite by manually specifying the output type to use:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'    #[Field(outputType: "ID")]\n')),(0,p.yg)("h2",{id:"usage"},"Usage"),(0,p.yg)("p",null,"The ",(0,p.yg)("inlineCode",{parentName:"p"},"outputType")," attribute will map the return value of the method to the output type passed in parameter."),(0,p.yg)("p",null,"You can use the ",(0,p.yg)("inlineCode",{parentName:"p"},"outputType")," attribute in the following annotations:"),(0,p.yg)("ul",null,(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"#[Query]")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"#[Mutation]")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"#[Subscription]")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"#[Field]")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"#[SourceField]")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"#[MagicField]"))),(0,p.yg)("h2",{id:"registering-a-custom-output-type-advanced"},"Registering a custom output type (advanced)"),(0,p.yg)("p",null,"In order to create a custom output type, you need to:"),(0,p.yg)("ol",null,(0,p.yg)("li",{parentName:"ol"},"Design a class that extends ",(0,p.yg)("inlineCode",{parentName:"li"},"GraphQL\\Type\\Definition\\ObjectType"),"."),(0,p.yg)("li",{parentName:"ol"},"Register this class in the GraphQL schema.")),(0,p.yg)("p",null,"You'll find more details on the ",(0,p.yg)("a",{parentName:"p",href:"https://webonyx.github.io/graphql-php/type-system/object-types/"},"Webonyx documentation"),"."),(0,p.yg)("hr",null),(0,p.yg)("p",null,"In order to find existing types, the schema is using ",(0,p.yg)("em",{parentName:"p"},"type mappers")," (classes implementing the ",(0,p.yg)("inlineCode",{parentName:"p"},"TypeMapperInterface")," interface)."),(0,p.yg)("p",null,"You need to make sure that one of these type mappers can return an instance of your type. The way you do this will depend on the framework\nyou use."),(0,p.yg)("h3",{id:"symfony-users"},"Symfony users"),(0,p.yg)("p",null,"Any class extending ",(0,p.yg)("inlineCode",{parentName:"p"},"GraphQL\\Type\\Definition\\ObjectType")," (and available in the container) will be automatically detected\nby Symfony and added to the schema."),(0,p.yg)("p",null,"If you want to automatically map the output type to a given PHP class, you will have to explicitly declare the output type\nas a service and use the ",(0,p.yg)("inlineCode",{parentName:"p"},"graphql.output_type")," tag:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-yaml"},"# config/services.yaml\nservices:\n    App\\MyOutputType:\n        tags:\n            - { name: 'graphql.output_type', class: 'App\\MyPhpClass' }\n")),(0,p.yg)("h3",{id:"other-frameworks"},"Other frameworks"),(0,p.yg)("p",null,"The easiest way is to use a ",(0,p.yg)("inlineCode",{parentName:"p"},"StaticTypeMapper"),". Use this class to register custom output types."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"// Sample code:\n$staticTypeMapper = new StaticTypeMapper(\n    // Let's register a type that maps by default to the \"MyClass\" PHP class\n    types: [\n        MyClass::class => new MyCustomOutputType()\n    ],\n\n    // If you don't want your output type to map to any PHP class by default, use:\n    notMappedTypes: [\n        new MyCustomOutputType()\n    ],\n);\n\n// Register the static type mapper in your application using the SchemaFactory instance\n$schemaFactory->addTypeMapper($staticTypeMapper);\n")),(0,p.yg)("h2",{id:"registering-a-custom-scalar-type-advanced"},"Registering a custom scalar type (advanced)"),(0,p.yg)("p",null,"If you need to add custom scalar types, first, check the ",(0,p.yg)("a",{parentName:"p",href:"https://github.com/thecodingmachine/graphqlite-misc-types"},"GraphQLite Misc. Types library"),'.\nIt contains a number of "out-of-the-box" scalar types ready to use and you might find what you need there.'),(0,p.yg)("p",null,"You still need to develop your custom scalar type? Ok, let's get started."),(0,p.yg)("p",null,"In order to add a scalar type in GraphQLite, you need to:"),(0,p.yg)("ul",null,(0,p.yg)("li",{parentName:"ul"},"create a ",(0,p.yg)("a",{parentName:"li",href:"https://webonyx.github.io/graphql-php/type-system/scalar-types/#writing-custom-scalar-types"},"Webonyx custom scalar type"),".\nYou do this by creating a class that extends ",(0,p.yg)("inlineCode",{parentName:"li"},"GraphQL\\Type\\Definition\\ScalarType"),"."),(0,p.yg)("li",{parentName:"ul"},'create a "type mapper" that will map PHP types to the GraphQL scalar type. You do this by writing a class implementing the ',(0,p.yg)("inlineCode",{parentName:"li"},"RootTypeMapperInterface"),"."),(0,p.yg)("li",{parentName:"ul"},'create a "type mapper factory" that will be in charge of creating your "type mapper".')),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"interface RootTypeMapperInterface\n{\n    /**\n     * @param \\ReflectionMethod|\\ReflectionProperty $reflector\n     */\n    public function toGraphQLOutputType(Type $type, ?OutputType $subType, $reflector, DocBlock $docBlockObj): OutputType;\n\n    /**\n     * @param \\ReflectionMethod|\\ReflectionProperty $reflector\n     */\n    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, $reflector, DocBlock $docBlockObj): InputType;\n\n    public function mapNameToType(string $typeName): NamedType;\n}\n")),(0,p.yg)("p",null,"The ",(0,p.yg)("inlineCode",{parentName:"p"},"toGraphQLOutputType")," and ",(0,p.yg)("inlineCode",{parentName:"p"},"toGraphQLInputType")," are meant to map a return type (for output types) or a parameter type (for input types)\nto your GraphQL scalar type. Return your scalar type if there is a match or ",(0,p.yg)("inlineCode",{parentName:"p"},"null")," if there no match."),(0,p.yg)("p",null,"The ",(0,p.yg)("inlineCode",{parentName:"p"},"mapNameToType")," should return your GraphQL scalar type if ",(0,p.yg)("inlineCode",{parentName:"p"},"$typeName")," is the name of your scalar type."),(0,p.yg)("p",null,"RootTypeMapper are organized ",(0,p.yg)("strong",{parentName:"p"},"in a chain")," (they are actually middlewares).\nEach instance of a ",(0,p.yg)("inlineCode",{parentName:"p"},"RootTypeMapper")," holds a reference on the next root type mapper to be called in the chain."),(0,p.yg)("p",null,"For instance:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'class AnyScalarTypeMapper implements RootTypeMapperInterface\n{\n    /** @var RootTypeMapperInterface */\n    private $next;\n\n    public function __construct(RootTypeMapperInterface $next)\n    {\n        $this->next = $next;\n    }\n\n    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType\n    {\n        if ($type instanceof Scalar) {\n            // AnyScalarType is a class implementing the Webonyx ScalarType type.\n            return AnyScalarType::getInstance();\n        }\n        // If the PHPDoc type is not "Scalar", let\'s pass the control to the next type mapper in the chain\n        return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);\n    }\n\n    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType\n    {\n        if ($type instanceof Scalar) {\n            // AnyScalarType is a class implementing the Webonyx ScalarType type.\n            return AnyScalarType::getInstance();\n        }\n        // If the PHPDoc type is not "Scalar", let\'s pass the control to the next type mapper in the chain\n        return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);\n    }\n\n    /**\n     * Returns a GraphQL type by name.\n     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should\n     * also map these types by name in the "mapNameToType" method.\n     *\n     * @param string $typeName The name of the GraphQL type\n     * @return NamedType|null\n     */\n    public function mapNameToType(string $typeName): ?NamedType\n    {\n        if ($typeName === AnyScalarType::NAME) {\n            return AnyScalarType::getInstance();\n        }\n        return null;\n    }\n}\n')),(0,p.yg)("p",null,"Now, in order to create an instance of your ",(0,p.yg)("inlineCode",{parentName:"p"},"AnyScalarTypeMapper")," class, you need an instance of the ",(0,p.yg)("inlineCode",{parentName:"p"},"$next")," type mapper in the chain.\nHow do you get the ",(0,p.yg)("inlineCode",{parentName:"p"},"$next")," type mapper? Through a factory:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"class AnyScalarTypeMapperFactory implements RootTypeMapperFactoryInterface\n{\n    public function create(RootTypeMapperInterface $next, RootTypeMapperFactoryContext $context): RootTypeMapperInterface\n    {\n        return new AnyScalarTypeMapper($next);\n    }\n}\n")),(0,p.yg)("p",null,"Now, you need to register this factory in your application, and we are done."),(0,p.yg)("p",null,"You can register your own root mapper factories using the ",(0,p.yg)("inlineCode",{parentName:"p"},"SchemaFactory::addRootTypeMapperFactory()")," method."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"$schemaFactory->addRootTypeMapperFactory(new AnyScalarTypeMapperFactory());\n")),(0,p.yg)("p",null,'If you are using the Symfony bundle, the factory will be automatically registered, you have nothing to do (the service\nis automatically tagged with the "graphql.root_type_mapper_factory" tag).'))}c.isMDXComponent=!0}}]);