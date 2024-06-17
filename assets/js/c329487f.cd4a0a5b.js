"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[9249],{29974:(e,a,n)=>{n.r(a),n.d(a,{assets:()=>s,contentTitle:()=>o,default:()=>u,frontMatter:()=>r,metadata:()=>l,toc:()=>p});var t=n(58168),i=(n(96540),n(15680));n(67443);const r={id:"changelog",title:"Changelog",sidebar_label:"Changelog",original_id:"changelog"},o=void 0,l={unversionedId:"changelog",id:"version-4.0/changelog",title:"Changelog",description:"4.0",source:"@site/versioned_docs/version-4.0/CHANGELOG.md",sourceDirName:".",slug:"/changelog",permalink:"/docs/4.0/changelog",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.0/CHANGELOG.md",tags:[],version:"4.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1718658882,formattedLastUpdatedAt:"Jun 17, 2024",frontMatter:{id:"changelog",title:"Changelog",sidebar_label:"Changelog",original_id:"changelog"},sidebar:"version-4.0/docs",previous:{title:"Semantic versioning",permalink:"/docs/4.0/semver"}},s={},p=[{value:"4.0",id:"40",level:2}],d={toc:p},g="wrapper";function u(e){let{components:a,...n}=e;return(0,i.yg)(g,(0,t.A)({},d,n,{components:a,mdxType:"MDXLayout"}),(0,i.yg)("h2",{id:"40"},"4.0"),(0,i.yg)("p",null,"This is a complete refactoring from 3.x. While existing annotations are kept compatible, the internals have completely\nchanged."),(0,i.yg)("p",null,"New features:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"You can directly ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/inheritance-interfaces#mapping-interfaces"},"annotate a PHP interface with ",(0,i.yg)("inlineCode",{parentName:"a"},"@Type")," to make it a GraphQL interface")),(0,i.yg)("li",{parentName:"ul"},"You can autowire services in resolvers, thanks to the new ",(0,i.yg)("inlineCode",{parentName:"li"},"@Autowire")," annotation"),(0,i.yg)("li",{parentName:"ul"},"Added ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/validation"},"user input validation")," (using the Symfony Validator or the Laravel validator or a custom ",(0,i.yg)("inlineCode",{parentName:"li"},"@Assertion")," annotation"),(0,i.yg)("li",{parentName:"ul"},"Improved security handling:",(0,i.yg)("ul",{parentName:"li"},(0,i.yg)("li",{parentName:"ul"},"Unauthorized access to fields can now generate GraphQL errors (rather that schema errors in GraphQLite v3)"),(0,i.yg)("li",{parentName:"ul"},"Added fine-grained security using the ",(0,i.yg)("inlineCode",{parentName:"li"},"@Security")," annotation. A field can now be ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/fine-grained-security"},"marked accessible or not depending on the context"),'.\nFor instance, you can restrict access to the field "viewsCount" of the type ',(0,i.yg)("inlineCode",{parentName:"li"},"BlogPost")," only for post that the current user wrote."),(0,i.yg)("li",{parentName:"ul"},"You can now inject the current logged user in any query / mutation / field using the ",(0,i.yg)("inlineCode",{parentName:"li"},"@InjectUser")," annotation"))),(0,i.yg)("li",{parentName:"ul"},"Performance:",(0,i.yg)("ul",{parentName:"li"},(0,i.yg)("li",{parentName:"ul"},"You can inject the ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/query-plan"},"Webonyx query plan in a parameter from a resolver")),(0,i.yg)("li",{parentName:"ul"},"You can use the ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/prefetch-method"},'dataloader pattern to improve performance drastically via the "prefetchMethod" attribute')))),(0,i.yg)("li",{parentName:"ul"},"Customizable error handling has been added:",(0,i.yg)("ul",{parentName:"li"},(0,i.yg)("li",{parentName:"ul"},"You can throw ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/error-handling"},"GraphQL errors")," with ",(0,i.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLException")),(0,i.yg)("li",{parentName:"ul"},'You can specify the HTTP response code to send with a given error, and the errors "extensions" section'),(0,i.yg)("li",{parentName:"ul"},"You can throw ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/error-handling#many-errors-for-one-exception"},"many errors in one exception")," with ",(0,i.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLAggregateException")))),(0,i.yg)("li",{parentName:"ul"},"You can map ",(0,i.yg)("a",{parentName:"li",href:"input-types#declaring-several-input-types-for-the-same-php-class"},"a given PHP class to several PHP input types")," (a PHP class can have several ",(0,i.yg)("inlineCode",{parentName:"li"},"@Factory")," annotations)"),(0,i.yg)("li",{parentName:"ul"},"You can force input types using ",(0,i.yg)("inlineCode",{parentName:"li"},'@UseInputType(for="$id", inputType="ID!")')),(0,i.yg)("li",{parentName:"ul"},"You can extend an input types (just like you could extend an output type in v3) using ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/extend_input_type"},"the new ",(0,i.yg)("inlineCode",{parentName:"a"},"@Decorate")," annotation")),(0,i.yg)("li",{parentName:"ul"},"In a factory, you can ",(0,i.yg)("a",{parentName:"li",href:"input-types#ignoring-some-parameters"},"exclude some optional parameters from the GraphQL schema"))),(0,i.yg)("p",null,"Many extension points have been added"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},'Added a "root type mapper" (useful to map scalar types to PHP types or to add custom annotations related to resolvers)'),(0,i.yg)("li",{parentName:"ul"},"Added ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/field-middlewares"},'"field middlewares"')," (useful to add middleware that modify the way GraphQL fields are handled)"),(0,i.yg)("li",{parentName:"ul"},"Added a ",(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/argument-resolving"},'"parameter type mapper"')," (useful to add customize parameter resolution or add custom annotations related to parameters)")),(0,i.yg)("p",null,"New framework specific features:"),(0,i.yg)("p",null,"Symfony:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},'The Symfony bundle now provides a "login" and a "logout" mutation (and also a "me" query)')),(0,i.yg)("p",null,"Laravel:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("a",{parentName:"li",href:"/docs/4.0/laravel-package-advanced#support-for-pagination"},"Native integration with the Laravel paginator")," has been added")),(0,i.yg)("p",null,"Internals:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"FieldsBuilder")," class has been split in many different services (",(0,i.yg)("inlineCode",{parentName:"li"},"FieldsBuilder"),", ",(0,i.yg)("inlineCode",{parentName:"li"},"TypeHandler"),", and a\nchain of ",(0,i.yg)("em",{parentName:"li"},"root type mappers"),")"),(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"FieldsBuilderFactory")," class has been completely removed."),(0,i.yg)("li",{parentName:"ul"},"Overall, there is not much in common internally between 4.x and 3.x. 4.x is much more flexible with many more hook points\nthan 3.x. Try it out!")))}u.isMDXComponent=!0}}]);