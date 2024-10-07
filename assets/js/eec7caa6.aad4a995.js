"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5282],{8384:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>u,contentTitle:()=>o,default:()=>g,frontMatter:()=>r,metadata:()=>s,toc:()=>l});var a=n(58168),i=(n(96540),n(15680));n(67443);const r={id:"authentication-authorization",title:"Authentication and authorization",sidebar_label:"Authentication and authorization"},o=void 0,s={unversionedId:"authentication-authorization",id:"authentication-authorization",title:"Authentication and authorization",description:"You might not want to expose your GraphQL API to anyone. Or you might want to keep some",source:"@site/docs/authentication-authorization.mdx",sourceDirName:".",slug:"/authentication-authorization",permalink:"/docs/next/authentication-authorization",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/authentication-authorization.mdx",tags:[],version:"current",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1728277176,formattedLastUpdatedAt:"Oct 7, 2024",frontMatter:{id:"authentication-authorization",title:"Authentication and authorization",sidebar_label:"Authentication and authorization"},sidebar:"docs",previous:{title:"User input validation",permalink:"/docs/next/validation"},next:{title:"Fine grained security",permalink:"/docs/next/fine-grained-security"}},u={},l=[{value:"<code>#[Logged]</code> and <code>#[Right]</code> attributes",id:"logged-and-right-attributes",level:2},{value:"Not throwing errors",id:"not-throwing-errors",level:2},{value:"Injecting the current user as a parameter",id:"injecting-the-current-user-as-a-parameter",level:2},{value:"Hiding fields / queries / mutations / subscriptions",id:"hiding-fields--queries--mutations--subscriptions",level:2}],d={toc:l},h="wrapper";function g(e){let{components:t,...n}=e;return(0,i.yg)(h,(0,a.A)({},d,n,{components:t,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"You might not want to expose your GraphQL API to anyone. Or you might want to keep some\nqueries/mutations/subscriptions or fields reserved to some users."),(0,i.yg)("p",null,"GraphQLite offers some control over what a user can do with your API. You can restrict access to\nresources:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"based on authentication using the ",(0,i.yg)("a",{parentName:"li",href:"#logged-and-right-annotations"},(0,i.yg)("inlineCode",{parentName:"a"},"#[Logged]")," attribute")," (restrict access to logged users)"),(0,i.yg)("li",{parentName:"ul"},"based on authorization using the ",(0,i.yg)("a",{parentName:"li",href:"#logged-and-right-annotations"},(0,i.yg)("inlineCode",{parentName:"a"},"#[Right]")," attribute")," (restrict access to logged users with certain rights)."),(0,i.yg)("li",{parentName:"ul"},"based on fine-grained authorization using the ",(0,i.yg)("a",{parentName:"li",href:"/docs/next/fine-grained-security"},(0,i.yg)("inlineCode",{parentName:"a"},"#[Security]")," attribute")," (restrict access for some given resources to some users).")),(0,i.yg)("div",{class:"alert alert--info"},"GraphQLite does not have its own security mechanism. Unless you're using our Symfony Bundle or our Laravel package, it is up to you to connect this feature to your framework's security mechanism.",(0,i.yg)("br",null),"See ",(0,i.yg)("a",{href:"implementing-security"},"Connecting GraphQLite to your framework's security module"),"."),(0,i.yg)("h2",{id:"logged-and-right-attributes"},(0,i.yg)("inlineCode",{parentName:"h2"},"#[Logged]")," and ",(0,i.yg)("inlineCode",{parentName:"h2"},"#[Right]")," attributes"),(0,i.yg)("p",null,"GraphQLite exposes two attributes (",(0,i.yg)("inlineCode",{parentName:"p"},"#[Logged]")," and ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Right]"),") that you can use to restrict access to a resource."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Logged;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Right;\n\nclass UserController\n{\n    /**\n     * @return User[]\n     */\n    #[Query]\n    #[Logged]\n    #[Right("CAN_VIEW_USER_LIST")]\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,i.yg)("p",null,"In the example above, the query ",(0,i.yg)("inlineCode",{parentName:"p"},"users")," will only be available if the user making the query is logged AND if he\nhas the ",(0,i.yg)("inlineCode",{parentName:"p"},"CAN_VIEW_USER_LIST")," right."),(0,i.yg)("p",null,(0,i.yg)("inlineCode",{parentName:"p"},"#[Logged]")," and ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Right]")," attributes can be used next to:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"#[Query]")," attributes"),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"#[Mutation]")," attributes"),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"#[Field]")," attributes")),(0,i.yg)("div",{class:"alert alert--info"},"By default, if a user tries to access an unauthorized query/mutation/subscription/field, an error is raised and the query fails."),(0,i.yg)("h2",{id:"not-throwing-errors"},"Not throwing errors"),(0,i.yg)("p",null,"If you do not want an error to be thrown when a user attempts to query a field/query/mutation/subscription\nthey have no access to, you can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[FailWith]")," attribute."),(0,i.yg)("p",null,"The ",(0,i.yg)("inlineCode",{parentName:"p"},"#[FailWith]")," attribute contains the value that will be returned for users with insufficient rights."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'class UserController\n{\n    /**\n     * If a user is not logged or if the user has not the right "CAN_VIEW_USER_LIST",\n     * the value returned will be "null".\n     *\n     * @return User[]\n     */\n    #[Query]\n    #[Logged]\n    #[Right("CAN_VIEW_USER_LIST")]\n    #[FailWith(value: null)]\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,i.yg)("h2",{id:"injecting-the-current-user-as-a-parameter"},"Injecting the current user as a parameter"),(0,i.yg)("p",null,"Use the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[InjectUser]")," attribute to get an instance of the current user logged in."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\nuse TheCodingMachine\\GraphQLite\\Annotations\\InjectUser;\n\nclass ProductController\n{\n    /**\n     * @return Product\n     */\n    #[Query]\n    public function product(\n            int $id,\n            #[InjectUser]\n            User $user\n        ): Product\n    {\n        // ...\n    }\n}\n")),(0,i.yg)("p",null,"The ",(0,i.yg)("inlineCode",{parentName:"p"},"#[InjectUser]")," attribute can be used next to:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"#[Query]")," attributes"),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"#[Mutation]")," attributes"),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"#[Field]")," attributes")),(0,i.yg)("p",null,"The object injected as the current user depends on your framework. It is in fact the object returned by the\n",(0,i.yg)("a",{parentName:"p",href:"/docs/next/implementing-security"},'"authentication service" configured in GraphQLite'),". If user is not authenticated and\nparameter's type is not nullable, an authorization exception is thrown, similar to ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Logged]")," attribute."),(0,i.yg)("h2",{id:"hiding-fields--queries--mutations--subscriptions"},"Hiding fields / queries / mutations / subscriptions"),(0,i.yg)("p",null,"By default, a user analysing the GraphQL schema can see all queries/mutations/subscriptions/types available.\nSome will be available to him and some won't."),(0,i.yg)("p",null,"If you want to add an extra level of security (or if you want your schema to be kept secret to unauthorized users),\nyou can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[HideIfUnauthorized]")," attribute. Beware of ",(0,i.yg)("a",{parentName:"p",href:"/docs/next/annotations-reference"},"it's limitations"),"."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'class UserController\n{\n    /**\n     * If a user is not logged or if the user has not the right "CAN_VIEW_USER_LIST",\n     * the schema will NOT contain the "users" query at all (so trying to call the\n     * "users" query will result in a GraphQL "query not found" error.\n     *\n     * @return User[]\n     */\n    #[Query]\n    #[Logged]\n    #[Right("CAN_VIEW_USER_LIST")]\n    #[HideIfUnauthorized]\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,i.yg)("p",null,"While this is the most secured mode, it can have drawbacks when working with development tools\n(you need to be logged as admin to fetch the complete schema)."),(0,i.yg)("div",{class:"alert alert--info"},'The "HideIfUnauthorized" mode was the default mode in GraphQLite 3 and is optional from GraphQLite 4+.'))}g.isMDXComponent=!0}}]);