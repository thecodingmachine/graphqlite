"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[742],{9152:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>u,contentTitle:()=>p,default:()=>c,frontMatter:()=>r,metadata:()=>s,toc:()=>d});var o=a(7462),l=(a(7294),a(3905)),n=(a(1839),a(4866)),i=a(5162);const r={id:"file-uploads",title:"File uploads",sidebar_label:"File uploads"},p=void 0,s={unversionedId:"file-uploads",id:"file-uploads",title:"File uploads",description:"GraphQL does not support natively the notion of file uploads, but an extension to the GraphQL protocol was proposed",source:"@site/docs/file-uploads.mdx",sourceDirName:".",slug:"/file-uploads",permalink:"/docs/next/file-uploads",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/file-uploads.mdx",tags:[],version:"current",lastUpdatedBy:"Jacob Thomason",lastUpdatedAt:1679057781,formattedLastUpdatedAt:"Mar 17, 2023",frontMatter:{id:"file-uploads",title:"File uploads",sidebar_label:"File uploads"},sidebar:"docs",previous:{title:"Prefetching records",permalink:"/docs/next/prefetch-method"},next:{title:"Pagination",permalink:"/docs/next/pagination"}},u={},d=[{value:"Installation",id:"installation",level:2},{value:"If you are using the Symfony bundle",id:"if-you-are-using-the-symfony-bundle",level:3},{value:"If you are using a PSR-15 compatible framework",id:"if-you-are-using-a-psr-15-compatible-framework",level:3},{value:"If you are using another framework not compatible with PSR-15",id:"if-you-are-using-another-framework-not-compatible-with-psr-15",level:3},{value:"Usage",id:"usage",level:2}],h={toc:d},m="wrapper";function c(e){let{components:t,...a}=e;return(0,l.kt)(m,(0,o.Z)({},h,a,{components:t,mdxType:"MDXLayout"}),(0,l.kt)("p",null,"GraphQL does not support natively the notion of file uploads, but an extension to the GraphQL protocol was proposed\nto add support for ",(0,l.kt)("a",{parentName:"p",href:"https://github.com/jaydenseric/graphql-multipart-request-spec"},"multipart requests"),"."),(0,l.kt)("h2",{id:"installation"},"Installation"),(0,l.kt)("p",null,"GraphQLite supports this extension through the use of the ",(0,l.kt)("a",{parentName:"p",href:"https://github.com/Ecodev/graphql-upload"},"Ecodev/graphql-upload")," library."),(0,l.kt)("p",null,"You must start by installing this package:"),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-console"},"$ composer require ecodev/graphql-upload\n")),(0,l.kt)("h3",{id:"if-you-are-using-the-symfony-bundle"},"If you are using the Symfony bundle"),(0,l.kt)("p",null,"If you are using our Symfony bundle, the file upload middleware is managed by the bundle. You have nothing to do\nand can start using it right away."),(0,l.kt)("h3",{id:"if-you-are-using-a-psr-15-compatible-framework"},"If you are using a PSR-15 compatible framework"),(0,l.kt)("p",null,"In order to use this, you must first be sure that the ",(0,l.kt)("inlineCode",{parentName:"p"},"ecodev/graphql-upload")," PSR-15 middleware is part of your middleware pipe."),(0,l.kt)("p",null,"Simply add ",(0,l.kt)("inlineCode",{parentName:"p"},"GraphQL\\Upload\\UploadMiddleware")," to your middleware pipe."),(0,l.kt)("h3",{id:"if-you-are-using-another-framework-not-compatible-with-psr-15"},"If you are using another framework not compatible with PSR-15"),(0,l.kt)("p",null,"Please check the Ecodev/graphql-upload library ",(0,l.kt)("a",{parentName:"p",href:"https://github.com/Ecodev/graphql-upload"},"documentation"),"\nfor more information on how to integrate it in your framework."),(0,l.kt)("h2",{id:"usage"},"Usage"),(0,l.kt)("p",null,"To handle an uploaded file, you type-hint against the PSR-7 ",(0,l.kt)("inlineCode",{parentName:"p"},"UploadedFileInterface"),":"),(0,l.kt)(n.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(i.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"class MyController\n{\n    #[Mutation]\n    public function saveDocument(string $name, UploadedFileInterface $file): Document\n    {\n        // Some code that saves the document.\n        $file->moveTo($someDir);\n    }\n}\n"))),(0,l.kt)(i.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"class MyController\n{\n    /**\n     * @Mutation\n     */\n    public function saveDocument(string $name, UploadedFileInterface $file): Document\n    {\n        // Some code that saves the document.\n        $file->moveTo($someDir);\n    }\n}\n")))),(0,l.kt)("p",null,"Of course, you need to use a GraphQL client that is compatible with multipart requests. See ",(0,l.kt)("a",{parentName:"p",href:"https://github.com/jaydenseric/graphql-multipart-request-spec#client"},"jaydenseric/graphql-multipart-request-spec")," for a list of compatible clients."),(0,l.kt)("p",null,"The GraphQL client must send the file using the Upload type."),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-graphql"},"mutation upload($file: Upload!) {\n    upload(file: $file)\n}\n")))}c.isMDXComponent=!0}}]);