"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[2600],{7385:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>u,contentTitle:()=>r,default:()=>g,frontMatter:()=>o,metadata:()=>s,toc:()=>l});var a=n(8168),i=(n(6540),n(5680));n(7443);const o={id:"authentication_authorization",title:"Authentication and authorization",sidebar_label:"Authentication and authorization",original_id:"authentication_authorization"},r=void 0,s={unversionedId:"authentication_authorization",id:"version-3.0/authentication_authorization",title:"Authentication and authorization",description:"You might not want to expose your GraphQL API to anyone. Or you might want to keep some queries/mutations or fields",source:"@site/versioned_docs/version-3.0/authentication_authorization.mdx",sourceDirName:".",slug:"/authentication_authorization",permalink:"/docs/3.0/authentication_authorization",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/authentication_authorization.mdx",tags:[],version:"3.0",lastUpdatedBy:"Olexandr Grynchuk",lastUpdatedAt:1710194766,formattedLastUpdatedAt:"Mar 11, 2024",frontMatter:{id:"authentication_authorization",title:"Authentication and authorization",sidebar_label:"Authentication and authorization",original_id:"authentication_authorization"},sidebar:"version-3.0/docs",previous:{title:"Extending a type",permalink:"/docs/3.0/extend_type"},next:{title:"External type declaration",permalink:"/docs/3.0/external_type_declaration"}},u={},l=[{value:"<code>@Logged</code> and <code>@Right</code> annotations",id:"logged-and-right-annotations",level:2},{value:"Constant schemas",id:"constant-schemas",level:2},{value:"Connecting GraphQLite to your framework&#39;s security module",id:"connecting-graphqlite-to-your-frameworks-security-module",level:2}],h={toc:l},c="wrapper";function g(e){let{components:t,...n}=e;return(0,i.yg)(c,(0,a.A)({},h,n,{components:t,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"You might not want to expose your GraphQL API to anyone. Or you might want to keep some queries/mutations or fields\nreserved to some users."),(0,i.yg)("p",null,"GraphQLite offers some control over what a user can do with your API based on authentication (whether the user\nis logged or not) or authorization (what rights the user have)."),(0,i.yg)("div",{class:"alert alert--info"},"GraphQLite does not have its own security mechanism. Unless you're using our Symfony Bundle, it is up to you to connect this feature to your framework's security mechanism.",(0,i.yg)("br",null),"See ",(0,i.yg)("a",{href:"#connecting-graphqlite-to-your-framework-s-security-module"},"Connecting GraphQLite to your framework's security module"),"."),(0,i.yg)("h2",{id:"logged-and-right-annotations"},(0,i.yg)("inlineCode",{parentName:"h2"},"@Logged")," and ",(0,i.yg)("inlineCode",{parentName:"h2"},"@Right")," annotations"),(0,i.yg)("p",null,"GraphQLite exposes two annotations (",(0,i.yg)("inlineCode",{parentName:"p"},"@Logged")," and ",(0,i.yg)("inlineCode",{parentName:"p"},"@Right"),") that you can use to restrict access to a resource."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Logged;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Right;\n\nclass UserController\n{\n    /**\n     * @Query\n     * @Logged\n     * @Right("CAN_VIEW_USER_LIST")\n     * @return User[]\n     */\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,i.yg)("p",null,"In the example above, the query ",(0,i.yg)("inlineCode",{parentName:"p"},"users")," will only be available if the user making the query is logged AND if he\nhas the ",(0,i.yg)("inlineCode",{parentName:"p"},"CAN_VIEW_USER_LIST")," right."),(0,i.yg)("p",null,(0,i.yg)("inlineCode",{parentName:"p"},"@Logged")," and ",(0,i.yg)("inlineCode",{parentName:"p"},"@Right")," annotations can be used next to:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"@Query")," annotations"),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"@Mutation")," annotations"),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"@Field")," annotations")),(0,i.yg)("div",{class:"alert alert--info"},"The query/mutation/field will NOT be part of the GraphQL schema if the current user is not logged or has not the requested right."),(0,i.yg)("h2",{id:"constant-schemas"},"Constant schemas"),(0,i.yg)("p",null,"By default, the schema will vary based on who is connected. This can be a problem with some GraphQL clients as the schema\nis changing from one user to another."),(0,i.yg)("p",null,"If you want to keep a constant schema, you can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"@FailWith")," annotation that contains the value that\nwill be returned for user with insufficient rights."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'class UserController\n{\n    /**\n     * If a user is not logged or if the user has not the right "CAN_VIEW_USER_LIST",\n     * the value returned will be "null".\n     *\n     * @Query\n     * @Logged\n     * @Right("CAN_VIEW_USER_LIST")\n     * @FailWith(null)\n     * @return User[]\n     */\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,i.yg)("h2",{id:"connecting-graphqlite-to-your-frameworks-security-module"},"Connecting GraphQLite to your framework's security module"),(0,i.yg)("div",{class:"alert alert--info"},"This step is NOT necessary for user using GraphQLite through the Symfony Bundle"),(0,i.yg)("p",null,"GraphQLite needs to know if a user is logged or not, and what rights it has.\nBut this is specific of the framework you use."),(0,i.yg)("p",null,"To plug GraphQLite to your framework's security mechanism, you will have to provide two classes implementing:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Security\\AuthenticationServiceInterface")),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Security\\AuthorizationServiceInterface"))),(0,i.yg)("p",null,"Those two interfaces act as adapters between GraphQLite and your framework:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'interface AuthenticationServiceInterface\n{\n    /**\n     * Returns true if the "current" user is logged.\n     *\n     * @return bool\n     */\n    public function isLogged(): bool;\n}\n')),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'interface AuthorizationServiceInterface\n{\n    /**\n     * Returns true if the "current" user has access to the right "$right".\n     *\n     * @param string $right\n     * @return bool\n     */\n    public function isAllowed(string $right): bool;\n}\n')))}g.isMDXComponent=!0}}]);