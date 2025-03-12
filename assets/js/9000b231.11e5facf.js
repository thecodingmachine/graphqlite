"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[2032],{1727:(e,a,t)=>{t.r(a),t.d(a,{assets:()=>s,contentTitle:()=>l,default:()=>u,frontMatter:()=>i,metadata:()=>o,toc:()=>p});var n=t(58168),r=(t(96540),t(15680));t(67443);const i={id:"migrating",title:"Release notes",sidebar_label:"Release notes",original_id:"migrating"},l=void 0,o={unversionedId:"migrating",id:"version-3.0/migrating",title:"Release notes",description:"First stable release of GraphQLite",source:"@site/versioned_docs/version-3.0/migrating.md",sourceDirName:".",slug:"/migrating",permalink:"/docs/3.0/migrating",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/migrating.md",tags:[],version:"3.0",lastUpdatedBy:"Jacob Thomason",lastUpdatedAt:1741747257,formattedLastUpdatedAt:"Mar 12, 2025",frontMatter:{id:"migrating",title:"Release notes",sidebar_label:"Release notes",original_id:"migrating"}},s={},p=[{value:"First stable release of GraphQLite",id:"first-stable-release-of-graphqlite",level:2},{value:"Basic example",id:"basic-example",level:2}],d={toc:p},g="wrapper";function u(e){let{components:a,...t}=e;return(0,r.yg)(g,(0,n.A)({},d,t,{components:a,mdxType:"MDXLayout"}),(0,r.yg)("h2",{id:"first-stable-release-of-graphqlite"},"First stable release of GraphQLite"),(0,r.yg)("p",null,"GraphQLite is PHP library that allows you to write your GraphQL queries in simple-to-write controllers."),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"Create a complete GraphQL API by simply annotating your PHP classes"),(0,r.yg)("li",{parentName:"ul"},"Framework agnostic, but Symfony and Laravel bindings available!"),(0,r.yg)("li",{parentName:"ul"},"Comes with batteries included: queries, mutations, mapping of arrays / iterators, file uploads, extendable types and more!")),(0,r.yg)("p",null,"After several months of work, we are very happy to announce the availability of GraphQLite v3.0."),(0,r.yg)("p",null,'If you are wondering where are v1 and v2... yeah... GraphQLite is a fork of "thecodingmachine/graphql-controllers" that already had a v1 and a v2. But so much has changed that it deserved a new name!'),(0,r.yg)("p",null,(0,r.yg)("a",{parentName:"p",href:"https://graphqlite.thecodingmachine.io"},"Check out the documentation")),(0,r.yg)("h2",{id:"basic-example"},"Basic example"),(0,r.yg)("p",null,"First, declare a query in your controller:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"class ProductController\n{\n    /**\n     * @Query()\n     */\n    public function product(string $id): Product\n    {\n        // Some code that looks for a product and returns it.\n    }\n}\n")),(0,r.yg)("p",null,"Then, annotate the ",(0,r.yg)("inlineCode",{parentName:"p"},"Product")," class to declare what fields are exposed to the GraphQL API:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type()\n */\nclass Product\n{\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n    // ...\n}\n")),(0,r.yg)("p",null,"That's it, you're good to go \ud83c\udf89! Query and enjoy!"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n  product(id: 42) {\n    name\n  }\n}\n")))}u.isMDXComponent=!0}}]);