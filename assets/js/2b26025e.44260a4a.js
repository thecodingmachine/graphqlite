"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[9127],{9025:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>s,contentTitle:()=>l,default:()=>c,frontMatter:()=>i,metadata:()=>o,toc:()=>d});var r=a(58168),n=(a(96540),a(15680));a(67443);const i={id:"index",title:"GraphQLite",slug:"/",sidebar_label:"GraphQLite"},l=void 0,o={unversionedId:"index",id:"version-6.1/index",title:"GraphQLite",description:"A PHP library that allows you to write your GraphQL queries in simple-to-write controllers.",source:"@site/versioned_docs/version-6.1/README.mdx",sourceDirName:".",slug:"/",permalink:"/docs/6.1/",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-6.1/README.mdx",tags:[],version:"6.1",lastUpdatedBy:"Oleksandr Prypkhan",lastUpdatedAt:1711930569,formattedLastUpdatedAt:"Apr 1, 2024",frontMatter:{id:"index",title:"GraphQLite",slug:"/",sidebar_label:"GraphQLite"},sidebar:"docs",next:{title:"Getting Started",permalink:"/docs/6.1/getting-started"}},s={},d=[{value:"Features",id:"features",level:2},{value:"Basic example",id:"basic-example",level:2}],p={toc:d},u="wrapper";function c(e){let{components:t,...a}=e;return(0,n.yg)(u,(0,r.A)({},p,a,{components:t,mdxType:"MDXLayout"}),(0,n.yg)("p",{align:"center"},(0,n.yg)("img",{src:"https://graphqlite.thecodingmachine.io/img/logo.svg",alt:"GraphQLite logo",width:"250",height:"250"})),(0,n.yg)("p",null,"A PHP library that allows you to write your GraphQL queries in simple-to-write controllers."),(0,n.yg)("h2",{id:"features"},"Features"),(0,n.yg)("ul",null,(0,n.yg)("li",{parentName:"ul"},"Create a complete GraphQL API by simply annotating your PHP classes"),(0,n.yg)("li",{parentName:"ul"},"Framework agnostic, but Symfony, Laravel and PSR-15 bindings available!"),(0,n.yg)("li",{parentName:"ul"},"Comes with batteries included: queries, mutations, mapping of arrays / iterators, file uploads, security, validation, extendable types and more!")),(0,n.yg)("h2",{id:"basic-example"},"Basic example"),(0,n.yg)("p",null,"First, declare a query in your controller:"),(0,n.yg)("pre",null,(0,n.yg)("code",{parentName:"pre",className:"language-php"},"class ProductController\n{\n    #[Query]\n    public function product(string $id): Product\n    {\n        // Some code that looks for a product and returns it.\n    }\n}\n")),(0,n.yg)("p",null,"Then, annotate the ",(0,n.yg)("inlineCode",{parentName:"p"},"Product")," class to declare what fields are exposed to the GraphQL API:"),(0,n.yg)("pre",null,(0,n.yg)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass Product\n{\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n    // ...\n}\n")),(0,n.yg)("p",null,"That's it, you're good to go! Query and enjoy!"),(0,n.yg)("pre",null,(0,n.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n  product(id: 42) {\n    name\n  }\n}\n")))}c.isMDXComponent=!0}}]);