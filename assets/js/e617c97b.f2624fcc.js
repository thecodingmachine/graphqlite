"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[3449],{26693:(e,a,n)=>{n.r(a),n.d(a,{assets:()=>s,contentTitle:()=>l,default:()=>g,frontMatter:()=>r,metadata:()=>o,toc:()=>p});var t=n(58168),i=(n(96540),n(15680));n(67443);const r={id:"changelog",title:"Changelog",sidebar_label:"Changelog",original_id:"changelog"},l=void 0,o={unversionedId:"changelog",id:"version-4.1/changelog",title:"Changelog",description:"4.1",source:"@site/versioned_docs/version-4.1/CHANGELOG.md",sourceDirName:".",slug:"/changelog",permalink:"/docs/4.1/changelog",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.1/CHANGELOG.md",tags:[],version:"4.1",lastUpdatedBy:"Aleksander Mahnert",lastUpdatedAt:1733463815,formattedLastUpdatedAt:"Dec 6, 2024",frontMatter:{id:"changelog",title:"Changelog",sidebar_label:"Changelog",original_id:"changelog"},sidebar:"version-4.1/docs",previous:{title:"Semantic versioning",permalink:"/docs/4.1/semver"}},s={},p=[{value:"4.1",id:"41",level:2},{value:"4.0",id:"40",level:2}],d={toc:p},u="wrapper";function g(e){let{components:a,...n}=e;return(0,i.yg)(u,(0,t.A)({},d,n,{components:a,mdxType:"MDXLayout"}),(0,i.yg)("h2",{id:"41"},"4.1"),(0,i.yg)("p",null,"Breaking change:"),(0,i.yg)("p",null,"There is one breaking change introduced in the minor version (this was important to allow PHP 8 compatibility)."),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("strong",{parentName:"li"},"ecodev/graphql-upload"),' package (used to get support for file uploads in GraphQL input types) is now a "recommended" dependency only.\nIf you are using GraphQL file uploads, you need to add ',(0,i.yg)("inlineCode",{parentName:"li"},"ecodev/graphql-upload")," to your ",(0,i.yg)("inlineCode",{parentName:"li"},"composer.json"),".")),(0,i.yg)("p",null,"New features:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"All annotations can now be accessed as PHP 8 attributes"),(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"@deprecated")," annotation in your PHP code translates into deprecated fields in your GraphQL schema"),(0,i.yg)("li",{parentName:"ul"},"You can now specify the GraphQL name of the Enum types you define"),(0,i.yg)("li",{parentName:"ul"},"Added the possibility to inject pure Webonyx objects in GraphQLite schema")),(0,i.yg)("p",null,"Minor changes:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"Migrated from ",(0,i.yg)("inlineCode",{parentName:"li"},"zend/diactoros")," to ",(0,i.yg)("inlineCode",{parentName:"li"},"laminas/diactoros")),(0,i.yg)("li",{parentName:"ul"},"Making the annotation cache directory configurable")),(0,i.yg)("p",null,"Miscellaneous:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"Migrated from Travis to Github actions")),(0,i.yg)("h2",{id:"40"},"4.0"),(0,i.yg)("p",null,"This is a complete refactoring from 3.x. While existing annotations are kept compatible, the internals have completely\nchanged."),(0,i.yg)("p",null,"New features:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"You can directly ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/inheritance-interfaces#mapping-interfaces"},"annotate a PHP interface with ",(0,i.yg)("inlineCode",{parentName:"a"},"@Type")," to make it a GraphQL interface")),(0,i.yg)("li",{parentName:"ul"},"You can autowire services in resolvers, thanks to the new ",(0,i.yg)("inlineCode",{parentName:"li"},"@Autowire")," annotation"),(0,i.yg)("li",{parentName:"ul"},"Added ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/validation"},"user input validation")," (using the Symfony Validator or the Laravel validator or a custom ",(0,i.yg)("inlineCode",{parentName:"li"},"@Assertion")," annotation"),(0,i.yg)("li",{parentName:"ul"},"Improved security handling:",(0,i.yg)("ul",{parentName:"li"},(0,i.yg)("li",{parentName:"ul"},"Unauthorized access to fields can now generate GraphQL errors (rather that schema errors in GraphQLite v3)"),(0,i.yg)("li",{parentName:"ul"},"Added fine-grained security using the ",(0,i.yg)("inlineCode",{parentName:"li"},"@Security")," annotation. A field can now be ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/fine-grained-security"},"marked accessible or not depending on the context"),'.\nFor instance, you can restrict access to the field "viewsCount" of the type ',(0,i.yg)("inlineCode",{parentName:"li"},"BlogPost")," only for post that the current user wrote."),(0,i.yg)("li",{parentName:"ul"},"You can now inject the current logged user in any query / mutation / field using the ",(0,i.yg)("inlineCode",{parentName:"li"},"@InjectUser")," annotation"))),(0,i.yg)("li",{parentName:"ul"},"Performance:",(0,i.yg)("ul",{parentName:"li"},(0,i.yg)("li",{parentName:"ul"},"You can inject the ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/query-plan"},"Webonyx query plan in a parameter from a resolver")),(0,i.yg)("li",{parentName:"ul"},"You can use the ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/prefetch-method"},'dataloader pattern to improve performance drastically via the "prefetchMethod" attribute')))),(0,i.yg)("li",{parentName:"ul"},"Customizable error handling has been added:",(0,i.yg)("ul",{parentName:"li"},(0,i.yg)("li",{parentName:"ul"},"You can throw ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/error-handling"},"GraphQL errors")," with ",(0,i.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLException")),(0,i.yg)("li",{parentName:"ul"},'You can specify the HTTP response code to send with a given error, and the errors "extensions" section'),(0,i.yg)("li",{parentName:"ul"},"You can throw ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/error-handling#many-errors-for-one-exception"},"many errors in one exception")," with ",(0,i.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLAggregateException")))),(0,i.yg)("li",{parentName:"ul"},"You can map ",(0,i.yg)("a",{parentName:"li",href:"input-types#declaring-several-input-types-for-the-same-php-class"},"a given PHP class to several PHP input types")," (a PHP class can have several ",(0,i.yg)("inlineCode",{parentName:"li"},"@Factory")," annotations)"),(0,i.yg)("li",{parentName:"ul"},"You can force input types using ",(0,i.yg)("inlineCode",{parentName:"li"},'@UseInputType(for="$id", inputType="ID!")')),(0,i.yg)("li",{parentName:"ul"},"You can extend an input types (just like you could extend an output type in v3) using ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/extend_input_type"},"the new ",(0,i.yg)("inlineCode",{parentName:"a"},"@Decorate")," annotation")),(0,i.yg)("li",{parentName:"ul"},"In a factory, you can ",(0,i.yg)("a",{parentName:"li",href:"input-types#ignoring-some-parameters"},"exclude some optional parameters from the GraphQL schema"))),(0,i.yg)("p",null,"Many extension points have been added"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},'Added a "root type mapper" (useful to map scalar types to PHP types or to add custom annotations related to resolvers)'),(0,i.yg)("li",{parentName:"ul"},"Added ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/field-middlewares"},'"field middlewares"')," (useful to add middleware that modify the way GraphQL fields are handled)"),(0,i.yg)("li",{parentName:"ul"},"Added a ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/argument-resolving"},'"parameter type mapper"')," (useful to add customize parameter resolution or add custom annotations related to parameters)")),(0,i.yg)("p",null,"New framework specific features:"),(0,i.yg)("p",null,"Symfony:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},'The Symfony bundle now provides a "login" and a "logout" mutation (and also a "me" query)')),(0,i.yg)("p",null,"Laravel:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("a",{parentName:"li",href:"/docs/4.1/laravel-package-advanced#support-for-pagination"},"Native integration with the Laravel paginator")," has been added")),(0,i.yg)("p",null,"Internals:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"FieldsBuilder")," class has been split in many different services (",(0,i.yg)("inlineCode",{parentName:"li"},"FieldsBuilder"),", ",(0,i.yg)("inlineCode",{parentName:"li"},"TypeHandler"),", and a\nchain of ",(0,i.yg)("em",{parentName:"li"},"root type mappers"),")"),(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"FieldsBuilderFactory")," class has been completely removed."),(0,i.yg)("li",{parentName:"ul"},"Overall, there is not much in common internally between 4.x and 3.x. 4.x is much more flexible with many more hook points\nthan 3.x. Try it out!")))}g.isMDXComponent=!0}}]);