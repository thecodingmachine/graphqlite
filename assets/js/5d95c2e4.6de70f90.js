"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[7146],{11302:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>s,contentTitle:()=>l,default:()=>c,frontMatter:()=>i,metadata:()=>o,toc:()=>d});var n=a(58168),r=(a(96540),a(15680));a(67443);const i={id:"index",title:"GraphQLite",slug:"/",sidebar_label:"GraphQLite"},l=void 0,o={unversionedId:"index",id:"version-8.0.0/index",title:"GraphQLite",description:"A PHP library that allows you to write your GraphQL queries in simple-to-write controllers.",source:"@site/versioned_docs/version-8.0.0/README.mdx",sourceDirName:".",slug:"/",permalink:"/docs/",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-8.0.0/README.mdx",tags:[],version:"8.0.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1734515092,formattedLastUpdatedAt:"Dec 18, 2024",frontMatter:{id:"index",title:"GraphQLite",slug:"/",sidebar_label:"GraphQLite"},sidebar:"docs",next:{title:"Getting Started",permalink:"/docs/getting-started"}},s={},d=[{value:"Features",id:"features",level:2},{value:"Basic example",id:"basic-example",level:2}],p={toc:d},u="wrapper";function c(e){let{components:t,...a}=e;return(0,r.yg)(u,(0,n.A)({},p,a,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("p",{align:"center"},(0,r.yg)("img",{src:"https://graphqlite.thecodingmachine.io/img/logo.svg",alt:"GraphQLite logo",width:"250",height:"250"})),(0,r.yg)("p",null,"A PHP library that allows you to write your GraphQL queries in simple-to-write controllers."),(0,r.yg)("h2",{id:"features"},"Features"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"Create a complete GraphQL API by simply annotating your PHP classes"),(0,r.yg)("li",{parentName:"ul"},"Framework agnostic, but Symfony, Laravel and PSR-15 bindings available!"),(0,r.yg)("li",{parentName:"ul"},"Comes with batteries included: queries, mutations, subscriptions, mapping of arrays / iterators,\nfile uploads, security, validation, extendable types and more!")),(0,r.yg)("h2",{id:"basic-example"},"Basic example"),(0,r.yg)("p",null,"First, declare a query in your controller:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"class ProductController\n{\n    #[Query]\n    public function product(string $id): Product\n    {\n        // Some code that looks for a product and returns it.\n    }\n}\n")),(0,r.yg)("p",null,"Then, annotate the ",(0,r.yg)("inlineCode",{parentName:"p"},"Product")," class to declare what fields are exposed to the GraphQL API:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass Product\n{\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n    // ...\n}\n")),(0,r.yg)("p",null,"That's it, you're good to go! Query and enjoy!"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n  product(id: 42) {\n    name\n  }\n}\n")))}c.isMDXComponent=!0}}]);