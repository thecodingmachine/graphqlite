"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[9917],{43698:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>o,contentTitle:()=>l,default:()=>h,frontMatter:()=>i,metadata:()=>s,toc:()=>p});var n=a(58168),r=(a(96540),a(15680));a(67443);const i={id:"queries",title:"Queries",sidebar_label:"Queries"},l=void 0,s={unversionedId:"queries",id:"version-8.0.0/queries",title:"Queries",description:"In GraphQLite, GraphQL queries are created by writing methods in controller classes.",source:"@site/versioned_docs/version-8.0.0/queries.mdx",sourceDirName:".",slug:"/queries",permalink:"/docs/queries",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-8.0.0/queries.mdx",tags:[],version:"8.0.0",lastUpdatedBy:"Jaap van Otterdijk",lastUpdatedAt:1742840357,formattedLastUpdatedAt:"Mar 24, 2025",frontMatter:{id:"queries",title:"Queries",sidebar_label:"Queries"},sidebar:"docs",previous:{title:"Other frameworks / No framework",permalink:"/docs/other-frameworks"},next:{title:"Mutations",permalink:"/docs/mutations"}},o={},p=[{value:"Simple query",id:"simple-query",level:2},{value:"About attributes",id:"about-attributes",level:2},{value:"Testing the query",id:"testing-the-query",level:2},{value:"Query with a type",id:"query-with-a-type",level:2}],u={toc:p},y="wrapper";function h(e){let{components:t,...i}=e;return(0,r.yg)(y,(0,n.A)({},u,i,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("p",null,"In GraphQLite, GraphQL queries are created by writing methods in ",(0,r.yg)("em",{parentName:"p"},"controller")," classes."),(0,r.yg)("p",null,"Those classes must be in the controllers namespaces which has been defined when you configured GraphQLite.\nFor instance, in Symfony, the controllers namespace is ",(0,r.yg)("inlineCode",{parentName:"p"},"App\\Controller")," by default."),(0,r.yg)("h2",{id:"simple-query"},"Simple query"),(0,r.yg)("p",null,"In a controller class, each query method must be annotated with the ",(0,r.yg)("inlineCode",{parentName:"p"},"#[Query]")," attribute. For instance:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass MyController\n{\n    #[Query]\n    public function hello(string $name): string\n    {\n        return 'Hello ' . $name;\n    }\n}\n")),(0,r.yg)("p",null,"This query is equivalent to the following ",(0,r.yg)("a",{parentName:"p",href:"https://graphql.org/learn/schema/#type-language"},"GraphQL type language"),":"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-graphql"},"Type Query {\n    hello(name: String!): String!\n}\n")),(0,r.yg)("p",null,"As you can see, GraphQLite will automatically do the mapping between PHP types and GraphQL types."),(0,r.yg)("div",{class:"alert alert--warning"},(0,r.yg)("strong",null,"Heads up!")," If you are not using a framework with an autowiring container (like Symfony or Laravel), please be aware that the ",(0,r.yg)("code",null,"MyController")," class must exist in the container of your application. Furthermore, the identifier of the controller in the container MUST be the fully qualified class name of controller."),(0,r.yg)("h2",{id:"about-attributes"},"About attributes"),(0,r.yg)("p",null,"GraphQLite relies a lot on attributes."),(0,r.yg)("p",null,"It supports the new PHP 8 attributes (",(0,r.yg)("inlineCode",{parentName:"p"},"#[Query]"),'), the "Doctrine annotations" style (',(0,r.yg)("inlineCode",{parentName:"p"},"#[Query]"),") was dropped."),(0,r.yg)("h2",{id:"testing-the-query"},"Testing the query"),(0,r.yg)("p",null,"The default GraphQL endpoint is ",(0,r.yg)("inlineCode",{parentName:"p"},"/graphql"),"."),(0,r.yg)("p",null,"The easiest way to test a GraphQL endpoint is to use ",(0,r.yg)("a",{parentName:"p",href:"https://github.com/graphql/graphiql"},"GraphiQL")," or\n",(0,r.yg)("a",{parentName:"p",href:"https://altair.sirmuel.design/"},"Altair")," clients (they are available as Chrome or Firefox plugins)"),(0,r.yg)("div",{class:"alert alert--info"},"If you are using the Symfony bundle, GraphiQL is also directly embedded.",(0,r.yg)("br",null),"Simply head to ",(0,r.yg)("code",null,"http://[path-to-my-app]/graphiql")),(0,r.yg)("p",null,"Here a query using our simple ",(0,r.yg)("em",{parentName:"p"},"Hello World")," example:"),(0,r.yg)("p",null,(0,r.yg)("img",{src:a(67258).A,width:"1132",height:"352"})),(0,r.yg)("h2",{id:"query-with-a-type"},"Query with a type"),(0,r.yg)("p",null,"So far, we simply declared a query. But we did not yet declare a type."),(0,r.yg)("p",null,"Let's assume you want to return a product:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass ProductController\n{\n    #[Query]\n    public function product(string $id): Product\n    {\n        // Some code that looks for a product and returns it.\n    }\n}\n")),(0,r.yg)("p",null,"As the ",(0,r.yg)("inlineCode",{parentName:"p"},"Product")," class is not a scalar type, you must tell GraphQLite how to handle it:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n#[Type]\nclass Product\n{\n    // ...\n\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    #[Field]\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n")),(0,r.yg)("p",null,"The ",(0,r.yg)("inlineCode",{parentName:"p"},"#[Type]")," attribute is used to inform GraphQLite that the ",(0,r.yg)("inlineCode",{parentName:"p"},"Product")," class is a GraphQL type."),(0,r.yg)("p",null,"The ",(0,r.yg)("inlineCode",{parentName:"p"},"#[Field]")," attribute is used to define the GraphQL fields. This attribute must be put on a ",(0,r.yg)("strong",{parentName:"p"},"public method"),"."),(0,r.yg)("p",null,"The ",(0,r.yg)("inlineCode",{parentName:"p"},"Product")," class must be in one of the ",(0,r.yg)("em",{parentName:"p"},"types")," namespaces. As for ",(0,r.yg)("em",{parentName:"p"},"controller")," classes, you configured this namespace when you installed\nGraphQLite. By default, in Symfony, the allowed types namespaces are ",(0,r.yg)("inlineCode",{parentName:"p"},"App\\Entity")," and ",(0,r.yg)("inlineCode",{parentName:"p"},"App\\Types"),"."),(0,r.yg)("p",null,"This query is equivalent to the following ",(0,r.yg)("a",{parentName:"p",href:"https://graphql.org/learn/schema/#type-language"},"GraphQL type language"),":"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-graphql"},"Type Product {\n    name: String!\n    price: Float\n}\n")),(0,r.yg)("div",{class:"alert alert--info"},(0,r.yg)("p",null,"If you are used to  ",(0,r.yg)("a",{href:"https://en.wikipedia.org/wiki/Domain-driven_design"},"Domain driven design"),", you probably realize that the ",(0,r.yg)("code",null,"Product")," class is part of your ",(0,r.yg)("i",null,"domain"),"."),(0,r.yg)("p",null,"GraphQL attributes are adding some serialization logic that is out of scope of the domain. These are ",(0,r.yg)("i",null,"just")," attributes and for most project, this is the fastest and easiest route."),(0,r.yg)("p",null,"If you feel that GraphQL attributes do not belong to the domain, or if you cannot modify the class directly (maybe because it is part of a third party library), there is another way to create types without annotating the domain class. We will explore that in the next chapter.")))}h.isMDXComponent=!0},67258:(e,t,a)=>{a.d(t,{A:()=>n});const n=a.p+"assets/images/query1-5a22bbe2398efcc725ea571a07ff2c9b.png"}}]);