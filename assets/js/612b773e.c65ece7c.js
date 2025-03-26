"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5646],{51054:(t,e,n)=>{n.r(e),n.d(e,{assets:()=>u,contentTitle:()=>s,default:()=>p,frontMatter:()=>o,metadata:()=>r,toc:()=>d});var a=n(58168),i=(n(96540),n(15680));n(67443);const o={id:"mutations",title:"Mutations",sidebar_label:"Mutations"},s=void 0,r={unversionedId:"mutations",id:"mutations",title:"Mutations",description:"In GraphQLite, mutations are created like queries.",source:"@site/docs/mutations.mdx",sourceDirName:".",slug:"/mutations",permalink:"/docs/next/mutations",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/mutations.mdx",tags:[],version:"current",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1742957246,formattedLastUpdatedAt:"Mar 26, 2025",frontMatter:{id:"mutations",title:"Mutations",sidebar_label:"Mutations"},sidebar:"docs",previous:{title:"Queries",permalink:"/docs/next/queries"},next:{title:"Subscriptions",permalink:"/docs/next/subscriptions"}},u={},d=[],l={toc:d},c="wrapper";function p(t){let{components:e,...n}=t;return(0,i.yg)(c,(0,a.A)({},l,n,{components:e,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"In GraphQLite, mutations are created ",(0,i.yg)("a",{parentName:"p",href:"/docs/next/queries"},"like queries"),"."),(0,i.yg)("p",null,"To create a mutation, you must annotate a method in a controller with the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Mutation]")," attribute."),(0,i.yg)("p",null,"For instance:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Mutation;\n\nclass ProductController\n{\n    #[Mutation]\n    public function saveProduct(int $id, string $name, ?float $price = null): Product\n    {\n        // Some code that saves a product.\n    }\n}\n")))}p.isMDXComponent=!0}}]);