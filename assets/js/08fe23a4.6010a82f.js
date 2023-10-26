"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[4196],{855:(t,e,n)=>{n.r(e),n.d(e,{assets:()=>u,contentTitle:()=>s,default:()=>c,frontMatter:()=>o,metadata:()=>r,toc:()=>d});var a=n(7462),i=(n(7294),n(3905));n(1839);const o={id:"mutations",title:"Mutations",sidebar_label:"Mutations"},s=void 0,r={unversionedId:"mutations",id:"version-6.1/mutations",title:"Mutations",description:"In GraphQLite, mutations are created like queries.",source:"@site/versioned_docs/version-6.1/mutations.mdx",sourceDirName:".",slug:"/mutations",permalink:"/docs/mutations",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-6.1/mutations.mdx",tags:[],version:"6.1",lastUpdatedBy:"Matja\u017e Lipu\u0161",lastUpdatedAt:1698333238,formattedLastUpdatedAt:"Oct 26, 2023",frontMatter:{id:"mutations",title:"Mutations",sidebar_label:"Mutations"},sidebar:"docs",previous:{title:"Queries",permalink:"/docs/queries"},next:{title:"Type mapping",permalink:"/docs/type-mapping"}},u={},d=[],p={toc:d},l="wrapper";function c(t){let{components:e,...n}=t;return(0,i.kt)(l,(0,a.Z)({},p,n,{components:e,mdxType:"MDXLayout"}),(0,i.kt)("p",null,"In GraphQLite, mutations are created ",(0,i.kt)("a",{parentName:"p",href:"/docs/queries"},"like queries"),"."),(0,i.kt)("p",null,"To create a mutation, you must annotate a method in a controller with the ",(0,i.kt)("inlineCode",{parentName:"p"},"@Mutation")," annotation."),(0,i.kt)("p",null,"For instance:"),(0,i.kt)("pre",null,(0,i.kt)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Mutation;\n\nclass ProductController\n{\n    #[Mutation]\n    public function saveProduct(int $id, string $name, ?float $price = null): Product\n    {\n        // Some code that saves a product.\n    }\n}\n")))}c.isMDXComponent=!0}}]);