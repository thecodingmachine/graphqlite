"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[1027],{65009:(e,n,a)=>{a.r(n),a.d(n,{assets:()=>o,contentTitle:()=>r,default:()=>g,frontMatter:()=>i,metadata:()=>u,toc:()=>y});var t=a(58168),p=(a(96540),a(15680)),l=(a(67443),a(11470)),s=a(19365);const i={id:"type-mapping",title:"Type mapping",sidebar_label:"Type mapping"},r=void 0,u={unversionedId:"type-mapping",id:"type-mapping",title:"Type mapping",description:"As explained in the queries section, the job of GraphQLite is to create GraphQL types from PHP types.",source:"@site/docs/type-mapping.mdx",sourceDirName:".",slug:"/type-mapping",permalink:"/docs/next/type-mapping",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/type-mapping.mdx",tags:[],version:"current",lastUpdatedBy:"Oleksandr Prypkhan",lastUpdatedAt:1711930569,formattedLastUpdatedAt:"Apr 1, 2024",frontMatter:{id:"type-mapping",title:"Type mapping",sidebar_label:"Type mapping"},sidebar:"docs",previous:{title:"Subscriptions",permalink:"/docs/next/subscriptions"},next:{title:"Autowiring services",permalink:"/docs/next/autowiring"}},o={},y=[{value:"Scalar mapping",id:"scalar-mapping",level:2},{value:"Class mapping",id:"class-mapping",level:2},{value:"Array mapping",id:"array-mapping",level:2},{value:"ID mapping",id:"id-mapping",level:2},{value:"Force the outputType",id:"force-the-outputtype",level:3},{value:"ID class",id:"id-class",level:3},{value:"Date mapping",id:"date-mapping",level:2},{value:"Union types",id:"union-types",level:2},{value:"Enum types",id:"enum-types",level:2},{value:"Enum types with myclabs/php-enum",id:"enum-types-with-myclabsphp-enum",level:3},{value:"Deprecation of fields",id:"deprecation-of-fields",level:2},{value:"More scalar types",id:"more-scalar-types",level:2}],m={toc:y},c="wrapper";function g(e){let{components:n,...a}=e;return(0,p.yg)(c,(0,t.A)({},m,a,{components:n,mdxType:"MDXLayout"}),(0,p.yg)("p",null,"As explained in the ",(0,p.yg)("a",{parentName:"p",href:"/docs/next/queries"},"queries")," section, the job of GraphQLite is to create GraphQL types from PHP types."),(0,p.yg)("h2",{id:"scalar-mapping"},"Scalar mapping"),(0,p.yg)("p",null,"Scalar PHP types can be type-hinted to the corresponding GraphQL types:"),(0,p.yg)("ul",null,(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"string")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"int")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"bool")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"float"))),(0,p.yg)("p",null,"For instance:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass MyController\n{\n    #[Query]\n    public function hello(string $name): string\n    {\n        return 'Hello ' . $name;\n    }\n}\n"))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass MyController\n{\n    /**\n     * @Query\n     */\n    public function hello(string $name): string\n    {\n        return 'Hello ' . $name;\n    }\n}\n")))),(0,p.yg)("h2",{id:"class-mapping"},"Class mapping"),(0,p.yg)("p",null,"When returning a PHP class in a query, you must annotate this class using ",(0,p.yg)("inlineCode",{parentName:"p"},"@Type")," and ",(0,p.yg)("inlineCode",{parentName:"p"},"@Field")," annotations:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n#[Type]\nclass Product\n{\n    // ...\n\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    #[Field]\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n"))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    /**\n     * @Field()\n     */\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n")))),(0,p.yg)("p",null,(0,p.yg)("strong",{parentName:"p"},"Note:")," The GraphQL output type name generated by GraphQLite is equal to the class name of the PHP class. So if your\nPHP class is ",(0,p.yg)("inlineCode",{parentName:"p"},"App\\Entities\\Product"),', then the GraphQL type will be named "Product".'),(0,p.yg)("p",null,'In case you have several types with the same class name in different namespaces, you will face a naming collision.\nHopefully, you can force the name of the GraphQL output type using the "name" attribute:'),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'#[Type(name: "MyProduct")]\nclass Product { /* ... */ }\n'))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type(name="MyProduct")\n */\nclass Product { /* ... */ }\n')))),(0,p.yg)("div",{class:"alert alert--info"},"You can also put a ",(0,p.yg)("a",{href:"inheritance-interfaces#mapping-interfaces"},(0,p.yg)("code",null,"@Type")," annotation on a PHP interface to map your code to a GraphQL interface"),"."),(0,p.yg)("h2",{id:"array-mapping"},"Array mapping"),(0,p.yg)("p",null,"You can type-hint against arrays (or iterators) as long as you add a detailed ",(0,p.yg)("inlineCode",{parentName:"p"},"@return")," statement in the PHPDoc."),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @return User[] <=== we specify that the array is an array of User objects.\n */\n#[Query]\npublic function users(int $limit, int $offset): array\n{\n    // Some code that returns an array of "users".\n}\n'))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Query\n * @return User[] <=== we specify that the array is an array of User objects.\n */\npublic function users(int $limit, int $offset): array\n{\n    // Some code that returns an array of "users".\n}\n')))),(0,p.yg)("h2",{id:"id-mapping"},"ID mapping"),(0,p.yg)("p",null,"GraphQL comes with a native ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," type. PHP has no such type."),(0,p.yg)("p",null,"There are two ways with GraphQLite to handle such type."),(0,p.yg)("h3",{id:"force-the-outputtype"},"Force the outputType"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'#[Field(outputType: "ID")]\npublic function getId(): string\n{\n    // ...\n}\n'))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Field(outputType="ID")\n */\npublic function getId(): string\n{\n    // ...\n}\n')))),(0,p.yg)("p",null,"Using the ",(0,p.yg)("inlineCode",{parentName:"p"},"outputType")," attribute of the ",(0,p.yg)("inlineCode",{parentName:"p"},"@Field")," annotation, you can force the output type to ",(0,p.yg)("inlineCode",{parentName:"p"},"ID"),"."),(0,p.yg)("p",null,"You can learn more about forcing output types in the ",(0,p.yg)("a",{parentName:"p",href:"/docs/next/custom-types"},"custom types section"),"."),(0,p.yg)("h3",{id:"id-class"},"ID class"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n#[Field]\npublic function getId(): ID\n{\n    // ...\n}\n"))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n/**\n * @Field\n */\npublic function getId(): ID\n{\n    // ...\n}\n")))),(0,p.yg)("p",null,"Note that you can also use the ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," class as an input type:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n#[Mutation]\npublic function save(ID $id, string $name): Product\n{\n    // ...\n}\n"))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n/**\n * @Mutation\n */\npublic function save(ID $id, string $name): Product\n{\n    // ...\n}\n")))),(0,p.yg)("h2",{id:"date-mapping"},"Date mapping"),(0,p.yg)("p",null,"Out of the box, GraphQL does not have a ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTime")," type, but we took the liberty to add one, with sensible defaults."),(0,p.yg)("p",null,"When used as an output type, ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTimeImmutable")," or ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTimeInterface")," PHP classes are\nautomatically mapped to this ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTime")," GraphQL type."),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"#[Field]\npublic function getDate(): \\DateTimeInterface\n{\n    return $this->date;\n}\n"))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Field\n */\npublic function getDate(): \\DateTimeInterface\n{\n    return $this->date;\n}\n")))),(0,p.yg)("p",null,"The ",(0,p.yg)("inlineCode",{parentName:"p"},"date")," field will be of type ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTime"),". In the returned JSON response to a query, the date is formatted as a string\nin the ",(0,p.yg)("strong",{parentName:"p"},"ISO8601")," format (aka ATOM format)."),(0,p.yg)("div",{class:"alert alert--danger"},"PHP ",(0,p.yg)("code",null,"DateTime")," type is not supported."),(0,p.yg)("h2",{id:"union-types"},"Union types"),(0,p.yg)("p",null,"Union types for return are supported in GraphQLite as of version 6.0:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"#[Query]\npublic function companyOrContact(int $id): Company|Contact\n{\n    // Some code that returns a company or a contact.\n}\n"))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Query\n * @return Company|Contact\n */\npublic function companyOrContact(int $id)\n{\n    // Some code that returns a company or a contact.\n}\n")))),(0,p.yg)("h2",{id:"enum-types"},"Enum types"),(0,p.yg)("p",null,"PHP 8.1 introduced native support for Enums.  GraphQLite now also supports native enums as of version 5.1."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"#[Type]\nenum Status: string\n{\n    case ON = 'on';\n    case OFF = 'off';\n    case PENDING = 'pending';\n}\n")),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @return User[]\n */\n#[Query]\npublic function users(Status $status): array\n{\n    if ($status === Status::ON) {\n        // Your logic\n    }\n    // ...\n}\n")),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-graphql"},"query users($status: Status!) {}\n    users(status: $status) {\n        id\n    }\n}\n")),(0,p.yg)("p",null,"By default, the name of the GraphQL enum type will be the name of the class. If you have a naming conflict (two classes\nthat live in different namespaces with the same class name), you can solve it using the ",(0,p.yg)("inlineCode",{parentName:"p"},"name")," property on the ",(0,p.yg)("inlineCode",{parentName:"p"},"@Type")," annotation:"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'namespace Model\\User;\n\n#[Type(name: "UserStatus")]\nenum Status: string\n{\n    // ...\n}\n')),(0,p.yg)("h3",{id:"enum-types-with-myclabsphp-enum"},"Enum types with myclabs/php-enum"),(0,p.yg)("div",{class:"alert alert--danger"},"This implementation is now deprecated and will be removed in the future.  You are advised to use native enums instead."),(0,p.yg)("p",null,(0,p.yg)("em",{parentName:"p"},"Prior to version 5.1, GraphQLite only supported Enums through the 3rd party library, ",(0,p.yg)("a",{parentName:"em",href:"https://github.com/myclabs/php-enum"},"myclabs/php-enum"),".  If you'd like to use this implementation you'll first need to add this library as a dependency to your application.")),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-bash"},"$ composer require myclabs/php-enum\n")),(0,p.yg)("p",null,"Now, any class extending the ",(0,p.yg)("inlineCode",{parentName:"p"},"MyCLabs\\Enum\\Enum")," class will be mapped to a GraphQL enum:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use MyCLabs\\Enum\\Enum;\n\nclass StatusEnum extends Enum\n{\n    private const ON = 'on';\n    private const OFF = 'off';\n    private const PENDING = 'pending';\n}\n")),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @return User[]\n */\n#[Query]\npublic function users(StatusEnum $status): array\n{\n    if ($status == StatusEnum::ON()) {\n        // Note that the "magic" ON() method returns an instance of the StatusEnum class.\n        // Also, note that we are comparing this instance using "==" (using "===" would fail as we have 2 different instances here)\n        // ...\n    }\n    // ...\n}\n'))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use MyCLabs\\Enum\\Enum;\n\nclass StatusEnum extends Enum\n{\n    private const ON = 'on';\n    private const OFF = 'off';\n    private const PENDING = 'pending';\n}\n")),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Query\n * @return User[]\n */\npublic function users(StatusEnum $status): array\n{\n    if ($status == StatusEnum::ON()) {\n        // Note that the "magic" ON() method returns an instance of the StatusEnum class.\n        // Also, note that we are comparing this instance using "==" (using "===" would fail as we have 2 different instances here)\n        // ...\n    }\n    // ...\n}\n')))),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-graphql"},"query users($status: StatusEnum!) {}\n    users(status: $status) {\n        id\n    }\n}\n")),(0,p.yg)("p",null,"By default, the name of the GraphQL enum type will be the name of the class. If you have a naming conflict (two classes\nthat live in different namespaces with the same class name), you can solve it using the ",(0,p.yg)("inlineCode",{parentName:"p"},"@EnumType")," annotation:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\EnumType;\n\n#[EnumType(name: "UserStatus")]\nclass StatusEnum extends Enum\n{\n    // ...\n}\n'))),(0,p.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\EnumType;\n\n/**\n * @EnumType(name="UserStatus")\n */\nclass StatusEnum extends Enum\n{\n    // ...\n}\n')))),(0,p.yg)("div",{class:"alert alert--warning"},'GraphQLite must be able to find all the classes extending the "MyCLabs\\Enum" class in your project. By default, GraphQLite will look for "Enum" classes in the namespaces declared for the types. For this reason, ',(0,p.yg)("strong",null,"your enum classes MUST be in one of the namespaces declared for the types in your GraphQLite configuration file.")),(0,p.yg)("h2",{id:"deprecation-of-fields"},"Deprecation of fields"),(0,p.yg)("p",null,"You can mark a field as deprecated in your GraphQL Schema by just annotating it with the ",(0,p.yg)("inlineCode",{parentName:"p"},"@deprecated")," PHPDoc annotation.  Note that a description (reason) is required for the annotation to be rendered."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    /**\n     * @Field()\n     * @deprecated use field `name` instead\n     */\n    public function getProductName(): string\n    {\n        return $this->name;\n    }\n}\n")),(0,p.yg)("p",null,"This will add the ",(0,p.yg)("inlineCode",{parentName:"p"},"@deprecated")," directive to the field in the GraphQL Schema which sets the ",(0,p.yg)("inlineCode",{parentName:"p"},"isDeprecated")," field to ",(0,p.yg)("inlineCode",{parentName:"p"},"true")," and adds the reason to the ",(0,p.yg)("inlineCode",{parentName:"p"},"deprecationReason")," field in an introspection query. Fields marked as deprecated can still be queried, but will be returned in an introspection query only if ",(0,p.yg)("inlineCode",{parentName:"p"},"includeDeprecated")," is set to ",(0,p.yg)("inlineCode",{parentName:"p"},"true"),"."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-graphql"},'query {\n    __type(name: "Product") {\n\ufffc       fields(includeDeprecated: true) {\n\ufffc           name\n\ufffc           isDeprecated\n\ufffc           deprecationReason\n\ufffc       }\n\ufffc   }\n}\n')),(0,p.yg)("h2",{id:"more-scalar-types"},"More scalar types"),(0,p.yg)("small",null,"Available in GraphQLite 4.0+"),(0,p.yg)("p",null,'GraphQL supports "custom" scalar types. GraphQLite supports adding more GraphQL scalar types.'),(0,p.yg)("p",null,"If you need more types, you can check the ",(0,p.yg)("a",{parentName:"p",href:"https://github.com/thecodingmachine/graphqlite-misc-types"},"GraphQLite Misc. Types library"),".\nIt adds support for more scalar types out of the box in GraphQLite."),(0,p.yg)("p",null,"Or if you have some special needs, ",(0,p.yg)("a",{parentName:"p",href:"custom-types#registering-a-custom-scalar-type-advanced"},"you can develop your own scalar types"),"."))}g.isMDXComponent=!0}}]);