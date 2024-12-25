"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[540],{13680:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>l,contentTitle:()=>o,default:()=>d,frontMatter:()=>r,metadata:()=>s,toc:()=>p});var n=a(58168),i=(a(96540),a(15680));a(67443);const r={id:"pagination",title:"Paginating large result sets",sidebar_label:"Pagination"},o=void 0,s={unversionedId:"pagination",id:"version-8.0.0/pagination",title:"Paginating large result sets",description:"It is quite common to have to paginate over large result sets.",source:"@site/versioned_docs/version-8.0.0/pagination.mdx",sourceDirName:".",slug:"/pagination",permalink:"/docs/pagination",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-8.0.0/pagination.mdx",tags:[],version:"8.0.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1735156155,formattedLastUpdatedAt:"Dec 25, 2024",frontMatter:{id:"pagination",title:"Paginating large result sets",sidebar_label:"Pagination"},sidebar:"docs",previous:{title:"File uploads",permalink:"/docs/file-uploads"},next:{title:"Custom types",permalink:"/docs/custom-types"}},l={},p=[{value:"Installation",id:"installation",level:2},{value:"Usage",id:"usage",level:2}],u={toc:p},g="wrapper";function d(e){let{components:t,...a}=e;return(0,i.yg)(g,(0,n.A)({},u,a,{components:t,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"It is quite common to have to paginate over large result sets."),(0,i.yg)("p",null,"GraphQLite offers a simple way to do that using ",(0,i.yg)("a",{parentName:"p",href:"https://github.com/beberlei/porpaginas"},"Porpaginas"),"."),(0,i.yg)("p",null,"Porpaginas is a set of PHP interfaces that can be implemented by result iterators. It comes with a native support for\nPHP arrays, Doctrine and ",(0,i.yg)("a",{parentName:"p",href:"https://thecodingmachine.github.io/tdbm/doc/limit_offset_resultset.html"},"TDBM"),"."),(0,i.yg)("div",{class:"alert alert--warning"},"If you are a Laravel user, Eloquent does not come with a Porpaginas iterator. However, ",(0,i.yg)("a",{href:"laravel-package-advanced"},"the GraphQLite Laravel bundle comes with its own pagination system"),"."),(0,i.yg)("h2",{id:"installation"},"Installation"),(0,i.yg)("p",null,"You will need to install the ",(0,i.yg)("a",{parentName:"p",href:"https://github.com/beberlei/porpaginas"},"Porpaginas")," library to benefit from this feature."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-bash"},"$ composer require beberlei/porpaginas\n")),(0,i.yg)("h2",{id:"usage"},"Usage"),(0,i.yg)("p",null,"In your query, simply return a class that implements ",(0,i.yg)("inlineCode",{parentName:"p"},"Porpaginas\\Result"),":"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class MyController\n{\n    /**\n     * @return Product[]\n     */\n    #[Query]\n    public function products(): Porpaginas\\Result\n    {\n        // Some code that returns a list of products\n\n        // If you are using Doctrine, something like:\n        return new Porpaginas\\Doctrine\\ORM\\ORMQueryResult($doctrineQuery);\n    }\n}\n")),(0,i.yg)("p",null,"Notice that:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"the method return type MUST BE ",(0,i.yg)("inlineCode",{parentName:"li"},"Porpaginas\\Result")," or a class implementing ",(0,i.yg)("inlineCode",{parentName:"li"},"Porpaginas\\Result")),(0,i.yg)("li",{parentName:"ul"},"you MUST add a ",(0,i.yg)("inlineCode",{parentName:"li"},"@return")," statement to help GraphQLite find the type of the list")),(0,i.yg)("p",null,"Once this is done, you can paginate directly from your GraphQL query:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-graphql"},"products {\n    items(limit: 10, offset: 20) {\n        id\n        name\n    }\n    count\n}\n")),(0,i.yg)("p",null,'Results are wrapped into an item field. You can use the "limit" and "offset" parameters to apply pagination automatically.'),(0,i.yg)("p",null,'The "count" field returns the ',(0,i.yg)("strong",{parentName:"p"},"total count")," of items."))}d.isMDXComponent=!0}}]);