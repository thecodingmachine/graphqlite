"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[9817],{4273:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>p,contentTitle:()=>i,default:()=>m,frontMatter:()=>u,metadata:()=>s,toc:()=>d});var a=n(7462),r=(n(7294),n(3905)),l=(n(1839),n(4866)),o=n(5162);const u={id:"query-plan",title:"Query plan",sidebar_label:"Query plan"},i=void 0,s={unversionedId:"query-plan",id:"query-plan",title:"Query plan",description:"The problem",source:"@site/docs/query-plan.mdx",sourceDirName:".",slug:"/query-plan",permalink:"/docs/next/query-plan",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/query-plan.mdx",tags:[],version:"current",lastUpdatedBy:"pvlg",lastUpdatedAt:1679520181,formattedLastUpdatedAt:"Mar 22, 2023",frontMatter:{id:"query-plan",title:"Query plan",sidebar_label:"Query plan"},sidebar:"docs",previous:{title:"Connecting security to your framework",permalink:"/docs/next/implementing-security"},next:{title:"Prefetching records",permalink:"/docs/next/prefetch-method"}},p={},d=[{value:"The problem",id:"the-problem",level:2},{value:"Fetching the query plan",id:"fetching-the-query-plan",level:2}],h={toc:d},c="wrapper";function m(e){let{components:t,...n}=e;return(0,r.kt)(c,(0,a.Z)({},h,n,{components:t,mdxType:"MDXLayout"}),(0,r.kt)("h2",{id:"the-problem"},"The problem"),(0,r.kt)("p",null,'GraphQL naive implementations often suffer from the "N+1" problem.'),(0,r.kt)("p",null,"Let's have a look at the following query:"),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-graphql"},"{\n    products {\n        name\n        manufacturer {\n            name\n        }\n    }\n}\n")),(0,r.kt)("p",null,"A naive implementation will do this:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"1 query to fetch the list of products"),(0,r.kt)("li",{parentName:"ul"},"1 query per product to fetch the manufacturer")),(0,r.kt)("p",null,'Assuming we have "N" products, we will make "N+1" queries.'),(0,r.kt)("p",null,'There are several ways to fix this problem. Assuming you are using a relational database, one solution is to try to look\nahead and perform only one query with a JOIN between "products" and "manufacturers".'),(0,r.kt)("p",null,'But how do I know if I should make the JOIN between "products" and "manufacturers" or not? I need to know ahead\nof time.'),(0,r.kt)("p",null,"With GraphQLite, you can answer this question by tapping into the ",(0,r.kt)("inlineCode",{parentName:"p"},"ResolveInfo")," object."),(0,r.kt)("h2",{id:"fetching-the-query-plan"},"Fetching the query plan"),(0,r.kt)("small",null,"Available in GraphQLite 4.0+"),(0,r.kt)(l.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.kt)(o.Z,{value:"php8",mdxType:"TabItem"},(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},"use GraphQL\\Type\\Definition\\ResolveInfo;\n\nclass ProductsController\n{\n    /**\n     * @return Product[]\n     */\n    #[Query]\n    public function products(ResolveInfo $info): array\n    {\n        if (isset($info->getFieldSelection()['manufacturer']) {\n            // Let's perform a request with a JOIN on manufacturer\n        } else {\n            // Let's perform a request without a JOIN on manufacturer\n        }\n        // ...\n    }\n}\n"))),(0,r.kt)(o.Z,{value:"php7",mdxType:"TabItem"},(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},"use GraphQL\\Type\\Definition\\ResolveInfo;\n\nclass ProductsController\n{\n    /**\n     * @Query\n     * @return Product[]\n     */\n    public function products(ResolveInfo $info): array\n    {\n        if (isset($info->getFieldSelection()['manufacturer']) {\n            // Let's perform a request with a JOIN on manufacturer\n        } else {\n            // Let's perform a request without a JOIN on manufacturer\n        }\n        // ...\n    }\n}\n")))),(0,r.kt)("p",null,(0,r.kt)("inlineCode",{parentName:"p"},"ResolveInfo")," is a class provided by Webonyx/GraphQL-PHP (the low-level GraphQL library used by GraphQLite).\nIt contains info about the query and what fields are requested. Using ",(0,r.kt)("inlineCode",{parentName:"p"},"ResolveInfo::getFieldSelection"),' you can analyze the query\nand decide whether you should perform additional "JOINS" in your query or not.'),(0,r.kt)("div",{class:"alert alert--info"},"As of the writing of this documentation, the ",(0,r.kt)("code",null,"ResolveInfo")," class is useful but somewhat limited. The ",(0,r.kt)("a",{href:"https://github.com/webonyx/graphql-php/pull/436"},'next version of Webonyx/GraphQL-PHP will add a "query plan"'),"that allows a deeper analysis of the query."))}m.isMDXComponent=!0}}]);