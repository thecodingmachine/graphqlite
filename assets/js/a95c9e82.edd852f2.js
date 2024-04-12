"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[8309],{40963:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>l,contentTitle:()=>p,default:()=>h,frontMatter:()=>r,metadata:()=>u,toc:()=>d});var a=n(58168),o=(n(96540),n(15680)),i=(n(67443),n(11470)),s=n(19365);const r={id:"mutations",title:"Mutations",sidebar_label:"Mutations"},p=void 0,u={unversionedId:"mutations",id:"version-4.3/mutations",title:"Mutations",description:"In GraphQLite, mutations are created like queries.",source:"@site/versioned_docs/version-4.3/mutations.mdx",sourceDirName:".",slug:"/mutations",permalink:"/docs/4.3/mutations",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.3/mutations.mdx",tags:[],version:"4.3",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1712955610,formattedLastUpdatedAt:"Apr 12, 2024",frontMatter:{id:"mutations",title:"Mutations",sidebar_label:"Mutations"},sidebar:"version-4.3/docs",previous:{title:"Queries",permalink:"/docs/4.3/queries"},next:{title:"Type mapping",permalink:"/docs/4.3/type-mapping"}},l={},d=[],c={toc:d},m="wrapper";function h(e){let{components:t,...n}=e;return(0,o.yg)(m,(0,a.A)({},c,n,{components:t,mdxType:"MDXLayout"}),(0,o.yg)("p",null,"In GraphQLite, mutations are created ",(0,o.yg)("a",{parentName:"p",href:"/docs/4.3/queries"},"like queries"),"."),(0,o.yg)("p",null,"To create a mutation, you must annotate a method in a controller with the ",(0,o.yg)("inlineCode",{parentName:"p"},"@Mutation")," annotation."),(0,o.yg)("p",null,"For instance:"),(0,o.yg)(i.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,o.yg)(s.A,{value:"php8",mdxType:"TabItem"},(0,o.yg)("pre",null,(0,o.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Mutation;\n\nclass ProductController\n{\n    #[Mutation]\n    public function saveProduct(int $id, string $name, ?float $price = null): Product\n    {\n        // Some code that saves a product.\n    }\n}\n"))),(0,o.yg)(s.A,{value:"php7",mdxType:"TabItem"},(0,o.yg)("pre",null,(0,o.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Mutation;\n\nclass ProductController\n{\n    /**\n     * @Mutation\n     */\n    public function saveProduct(int $id, string $name, ?float $price = null): Product\n    {\n        // Some code that saves a product.\n    }\n}\n")))))}h.isMDXComponent=!0}}]);