"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[8267],{60331:(e,n,t)=>{t.r(n),t.d(n,{assets:()=>s,contentTitle:()=>o,default:()=>d,frontMatter:()=>l,metadata:()=>u,toc:()=>r});var a=t(58168),i=(t(96540),t(15680));t(67443);const l={id:"symfony-bundle-advanced",title:"Symfony bundle: advanced usage",sidebar_label:"Symfony specific features",original_id:"symfony-bundle-advanced"},o=void 0,u={unversionedId:"symfony-bundle-advanced",id:"version-4.0/symfony-bundle-advanced",title:"Symfony bundle: advanced usage",description:"The Symfony bundle comes with a number of features to ease the integration of GraphQLite in Symfony.",source:"@site/versioned_docs/version-4.0/symfony-bundle-advanced.mdx",sourceDirName:".",slug:"/symfony-bundle-advanced",permalink:"/docs/4.0/symfony-bundle-advanced",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.0/symfony-bundle-advanced.mdx",tags:[],version:"4.0",lastUpdatedBy:"sudevva",lastUpdatedAt:1728787444,formattedLastUpdatedAt:"Oct 13, 2024",frontMatter:{id:"symfony-bundle-advanced",title:"Symfony bundle: advanced usage",sidebar_label:"Symfony specific features",original_id:"symfony-bundle-advanced"},sidebar:"version-4.0/docs",previous:{title:"Class with multiple output types",permalink:"/docs/4.0/multiple_output_types"},next:{title:"Laravel specific features",permalink:"/docs/4.0/laravel-package-advanced"}},s={},r=[{value:"Login and logout",id:"login-and-logout",level:2},{value:"Login using the &quot;login&quot; mutation",id:"login-using-the-login-mutation",level:3},{value:"Get the current user with the &quot;me&quot; query",id:"get-the-current-user-with-the-me-query",level:3},{value:"Logout using the &quot;logout&quot; mutation",id:"logout-using-the-logout-mutation",level:3},{value:"Injecting the Request",id:"injecting-the-request",level:2}],g={toc:r},y="wrapper";function d(e){let{components:n,...t}=e;return(0,i.yg)(y,(0,a.A)({},g,t,{components:n,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"The Symfony bundle comes with a number of features to ease the integration of GraphQLite in Symfony."),(0,i.yg)("h2",{id:"login-and-logout"},"Login and logout"),(0,i.yg)("p",null,'Out of the box, the GraphQLite bundle will expose a "login" and a "logout" mutation as well\nas a "me" query (that returns the current user).'),(0,i.yg)("p",null,'If you need to customize this behaviour, you can edit the "graphqlite.security" configuration key.'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    enable_login: auto # Default setting\n    enable_me: auto # Default setting\n")),(0,i.yg)("p",null,'By default, GraphQLite will enable "login" and "logout" mutations and the "me" query if the following conditions are met:'),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},'the "security" bundle is installed and configured (with a security provider and encoder)'),(0,i.yg)("li",{parentName:"ul"},'the "session" support is enabled (via the "framework.session.enabled" key).')),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    enable_login: on\n")),(0,i.yg)("p",null,"By settings ",(0,i.yg)("inlineCode",{parentName:"p"},"enable_login=on"),", you are stating that you explicitly want the login/logout mutations.\nIf one of the dependencies is missing, an exception is thrown (unlike in default mode where the mutations\nare silently discarded)."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    enable_login: off\n")),(0,i.yg)("p",null,"Use the ",(0,i.yg)("inlineCode",{parentName:"p"},"enable_login=off")," to disable the mutations."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    firewall_name: main # default value\n")),(0,i.yg)("p",null,'By default, GraphQLite assumes that your firewall name is "main". This is the default value used in the\nSymfony security bundle so it is likely the value you are using. If for some reason you want to use\nanother firewall, configure the name with ',(0,i.yg)("inlineCode",{parentName:"p"},"graphqlite.security.firewall_name"),"."),(0,i.yg)("h3",{id:"login-using-the-login-mutation"},'Login using the "login" mutation'),(0,i.yg)("p",null,"The mutation below will log-in a user:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-graphql"},'mutation login {\n  login(userName:"foo", password:"bar") {\n    userName\n    roles\n  }\n}\n')),(0,i.yg)("h3",{id:"get-the-current-user-with-the-me-query"},'Get the current user with the "me" query'),(0,i.yg)("p",null,'Retrieving the current user is easy with the "me" query:'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n  me {\n    userName\n    roles\n  }\n}\n")),(0,i.yg)("p",null,"In Symfony, user objects implement ",(0,i.yg)("inlineCode",{parentName:"p"},"Symfony\\Component\\Security\\Core\\User\\UserInterface"),".\nThis interface is automatically mapped to a type with 2 fields:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"userName: String!")),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("inlineCode",{parentName:"li"},"roles: [String!]!"))),(0,i.yg)("p",null,"If you want to get more fields, just add the ",(0,i.yg)("inlineCode",{parentName:"p"},"@Type")," annotation to your user class:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type\n */\nclass User implements UserInterface\n{\n    /**\n     * @Field\n     */\n    public function getEmail() : string\n    {\n        // ...\n    }\n\n}\n")),(0,i.yg)("p",null,"You can now query this field using an ",(0,i.yg)("a",{parentName:"p",href:"https://graphql.org/learn/queries/#inline-fragments"},"inline fragment"),":"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n  me {\n    userName\n    roles\n    ... on User {\n      email\n    }\n  }\n}\n")),(0,i.yg)("h3",{id:"logout-using-the-logout-mutation"},'Logout using the "logout" mutation'),(0,i.yg)("p",null,'Use the "logout" mutation to log a user out'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-graphql"},"mutation logout {\n  logout\n}\n")),(0,i.yg)("h2",{id:"injecting-the-request"},"Injecting the Request"),(0,i.yg)("p",null,"You can inject the Symfony Request object in any query/mutation/field."),(0,i.yg)("p",null,"Most of the time, getting the request object is irrelevant. Indeed, it is GraphQLite's job to parse this request and\nmanage it for you. Sometimes yet, fetching the request can be needed. In those cases, simply type-hint on the request\nin any parameter of your query/mutation/field."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"use Symfony\\Component\\HttpFoundation\\Request;\n\n/**\n * @Query\n */\npublic function getUser(int $id, Request $request): User\n{\n    // The $request object contains the Symfony Request.\n}\n")))}d.isMDXComponent=!0}}]);