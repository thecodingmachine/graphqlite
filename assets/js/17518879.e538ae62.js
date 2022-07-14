"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[7040],{3905:(e,t,n)=>{n.d(t,{Zo:()=>u,kt:()=>m});var a=n(67294);function r(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function l(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){r(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function o(e,t){if(null==e)return{};var n,a,r=function(e,t){if(null==e)return{};var n,a,r={},i=Object.keys(e);for(a=0;a<i.length;a++)n=i[a],t.indexOf(n)>=0||(r[n]=e[n]);return r}(e,t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);for(a=0;a<i.length;a++)n=i[a],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(r[n]=e[n])}return r}var p=a.createContext({}),d=function(e){var t=a.useContext(p),n=t;return e&&(n="function"==typeof e?e(t):l(l({},t),e)),n},u=function(e){var t=d(e.components);return a.createElement(p.Provider,{value:t},e.children)},c={inlineCode:"code",wrapper:function(e){var t=e.children;return a.createElement(a.Fragment,{},t)}},s=a.forwardRef((function(e,t){var n=e.components,r=e.mdxType,i=e.originalType,p=e.parentName,u=o(e,["components","mdxType","originalType","parentName"]),s=d(n),m=r,h=s["".concat(p,".").concat(m)]||s[m]||c[m]||i;return n?a.createElement(h,l(l({ref:t},u),{},{components:n})):a.createElement(h,l({ref:t},u))}));function m(e,t){var n=arguments,r=t&&t.mdxType;if("string"==typeof e||r){var i=n.length,l=new Array(i);l[0]=s;var o={};for(var p in t)hasOwnProperty.call(t,p)&&(o[p]=t[p]);o.originalType=e,o.mdxType="string"==typeof e?e:r,l[1]=o;for(var d=2;d<i;d++)l[d]=n[d];return a.createElement.apply(null,l)}return a.createElement.apply(null,n)}s.displayName="MDXCreateElement"},43872:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>p,contentTitle:()=>l,default:()=>c,frontMatter:()=>i,metadata:()=>o,toc:()=>d});var a=n(87462),r=(n(67294),n(3905));const i={id:"changelog",title:"Changelog",sidebar_label:"Changelog"},l=void 0,o={unversionedId:"changelog",id:"version-4.3/changelog",title:"Changelog",description:"4.3.0",source:"@site/versioned_docs/version-4.3/CHANGELOG.md",sourceDirName:".",slug:"/changelog",permalink:"/docs/4.3/changelog",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.3/CHANGELOG.md",tags:[],version:"4.3",lastUpdatedBy:"bladl",lastUpdatedAt:1657811576,formattedLastUpdatedAt:"7/14/2022",frontMatter:{id:"changelog",title:"Changelog",sidebar_label:"Changelog"},sidebar:"version-4.3/docs",previous:{title:"Semantic versioning",permalink:"/docs/4.3/semver"}},p={},d=[{value:"4.3.0",id:"430",level:2},{value:"Breaking change:",id:"breaking-change",level:4},{value:"Minor changes:",id:"minor-changes",level:4},{value:"4.2.0",id:"420",level:2},{value:"Breaking change:",id:"breaking-change-1",level:4},{value:"New features:",id:"new-features",level:4},{value:"4.1.0",id:"410",level:2},{value:"Breaking change:",id:"breaking-change-2",level:4},{value:"New features:",id:"new-features-1",level:4},{value:"Minor changes:",id:"minor-changes-1",level:4},{value:"Miscellaneous:",id:"miscellaneous",level:4},{value:"4.0.0",id:"400",level:2},{value:"New features:",id:"new-features-2",level:4},{value:"Symfony:",id:"symfony",level:4},{value:"Laravel:",id:"laravel",level:4},{value:"Internals:",id:"internals",level:4}],u={toc:d};function c(e){let{components:t,...n}=e;return(0,r.kt)("wrapper",(0,a.Z)({},u,n,{components:t,mdxType:"MDXLayout"}),(0,r.kt)("h2",{id:"430"},"4.3.0"),(0,r.kt)("h4",{id:"breaking-change"},"Breaking change:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"The method ",(0,r.kt)("inlineCode",{parentName:"li"},"setAnnotationCacheDir($directory)")," has been removed from the ",(0,r.kt)("inlineCode",{parentName:"li"},"SchemaFactory"),".  The annotation\ncache will use your ",(0,r.kt)("inlineCode",{parentName:"li"},"Psr\\SimpleCache\\CacheInterface")," compliant cache handler set through the ",(0,r.kt)("inlineCode",{parentName:"li"},"SchemaFactory"),"\nconstructor.")),(0,r.kt)("h4",{id:"minor-changes"},"Minor changes:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"Removed dependency for doctrine/cache and unified some of the cache layers following a PSR interface."),(0,r.kt)("li",{parentName:"ul"},"Cleaned up some of the documentation in an attempt to get things accurate with versioned releases.")),(0,r.kt)("h2",{id:"420"},"4.2.0"),(0,r.kt)("h4",{id:"breaking-change-1"},"Breaking change:"),(0,r.kt)("p",null,"The method signature for ",(0,r.kt)("inlineCode",{parentName:"p"},"toGraphQLOutputType")," and ",(0,r.kt)("inlineCode",{parentName:"p"},"toGraphQLInputType")," have been changed to the following:"),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * @param \\ReflectionMethod|\\ReflectionProperty $reflector\n */\npublic function toGraphQLOutputType(Type $type, ?OutputType $subType, $reflector, DocBlock $docBlockObj): OutputType;\n\n/**\n * @param \\ReflectionMethod|\\ReflectionProperty $reflector\n */\npublic function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, $reflector, DocBlock $docBlockObj): InputType;\n")),(0,r.kt)("h4",{id:"new-features"},"New features:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/annotations-reference#input-annotation"},"@Input")," annotation is introduced as an alternative to ",(0,r.kt)("inlineCode",{parentName:"li"},"@Factory"),". Now GraphQL input type can be created in the same manner as ",(0,r.kt)("inlineCode",{parentName:"li"},"@Type")," in combination with ",(0,r.kt)("inlineCode",{parentName:"li"},"@Field")," - ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/input-types#input-annotation"},"example"),"."),(0,r.kt)("li",{parentName:"ul"},"New attributes has been added to ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/annotations-reference#field-annotation"},"@Field")," annotation: ",(0,r.kt)("inlineCode",{parentName:"li"},"for"),", ",(0,r.kt)("inlineCode",{parentName:"li"},"inputType")," and ",(0,r.kt)("inlineCode",{parentName:"li"},"description"),"."),(0,r.kt)("li",{parentName:"ul"},"The following annotations now can be applied to class properties directly: ",(0,r.kt)("inlineCode",{parentName:"li"},"@Field"),", ",(0,r.kt)("inlineCode",{parentName:"li"},"@Logged"),", ",(0,r.kt)("inlineCode",{parentName:"li"},"@Right"),", ",(0,r.kt)("inlineCode",{parentName:"li"},"@FailWith"),", ",(0,r.kt)("inlineCode",{parentName:"li"},"@HideIfUnauthorized")," and ",(0,r.kt)("inlineCode",{parentName:"li"},"@Security"),".")),(0,r.kt)("h2",{id:"410"},"4.1.0"),(0,r.kt)("h4",{id:"breaking-change-2"},"Breaking change:"),(0,r.kt)("p",null,"There is one breaking change introduced in the minor version (this was important to allow PHP 8 compatibility)."),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"The ",(0,r.kt)("strong",{parentName:"li"},"ecodev/graphql-upload"),' package (used to get support for file uploads in GraphQL input types) is now a "recommended" dependency only.\nIf you are using GraphQL file uploads, you need to add ',(0,r.kt)("inlineCode",{parentName:"li"},"ecodev/graphql-upload")," to your ",(0,r.kt)("inlineCode",{parentName:"li"},"composer.json"),".")),(0,r.kt)("h4",{id:"new-features-1"},"New features:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"All annotations can now be accessed as PHP 8 attributes"),(0,r.kt)("li",{parentName:"ul"},"The ",(0,r.kt)("inlineCode",{parentName:"li"},"@deprecated")," annotation in your PHP code translates into deprecated fields in your GraphQL schema"),(0,r.kt)("li",{parentName:"ul"},"You can now specify the GraphQL name of the Enum types you define"),(0,r.kt)("li",{parentName:"ul"},"Added the possibility to inject pure Webonyx objects in GraphQLite schema")),(0,r.kt)("h4",{id:"minor-changes-1"},"Minor changes:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"Migrated from ",(0,r.kt)("inlineCode",{parentName:"li"},"zend/diactoros")," to ",(0,r.kt)("inlineCode",{parentName:"li"},"laminas/diactoros")),(0,r.kt)("li",{parentName:"ul"},"Making the annotation cache directory configurable")),(0,r.kt)("h4",{id:"miscellaneous"},"Miscellaneous:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"Migrated from Travis to Github actions")),(0,r.kt)("h2",{id:"400"},"4.0.0"),(0,r.kt)("p",null,"This is a complete refactoring from 3.x. While existing annotations are kept compatible, the internals have completely\nchanged."),(0,r.kt)("h4",{id:"new-features-2"},"New features:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"You can directly ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/inheritance-interfaces#mapping-interfaces"},"annotate a PHP interface with ",(0,r.kt)("inlineCode",{parentName:"a"},"@Type")," to make it a GraphQL interface")),(0,r.kt)("li",{parentName:"ul"},"You can autowire services in resolvers, thanks to the new ",(0,r.kt)("inlineCode",{parentName:"li"},"@Autowire")," annotation"),(0,r.kt)("li",{parentName:"ul"},"Added ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/validation"},"user input validation")," (using the Symfony Validator or the Laravel validator or a custom ",(0,r.kt)("inlineCode",{parentName:"li"},"@Assertion")," annotation"),(0,r.kt)("li",{parentName:"ul"},"Improved security handling:",(0,r.kt)("ul",{parentName:"li"},(0,r.kt)("li",{parentName:"ul"},"Unauthorized access to fields can now generate GraphQL errors (rather that schema errors in GraphQLite v3)"),(0,r.kt)("li",{parentName:"ul"},"Added fine-grained security using the ",(0,r.kt)("inlineCode",{parentName:"li"},"@Security")," annotation. A field can now be ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/fine-grained-security"},"marked accessible or not depending on the context"),'.\nFor instance, you can restrict access to the field "viewsCount" of the type ',(0,r.kt)("inlineCode",{parentName:"li"},"BlogPost")," only for post that the current user wrote."),(0,r.kt)("li",{parentName:"ul"},"You can now inject the current logged user in any query / mutation / field using the ",(0,r.kt)("inlineCode",{parentName:"li"},"@InjectUser")," annotation"))),(0,r.kt)("li",{parentName:"ul"},"Performance:",(0,r.kt)("ul",{parentName:"li"},(0,r.kt)("li",{parentName:"ul"},"You can inject the ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/query-plan"},"Webonyx query plan in a parameter from a resolver")),(0,r.kt)("li",{parentName:"ul"},"You can use the ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/prefetch-method"},'dataloader pattern to improve performance drastically via the "prefetchMethod" attribute')))),(0,r.kt)("li",{parentName:"ul"},"Customizable error handling has been added:",(0,r.kt)("ul",{parentName:"li"},(0,r.kt)("li",{parentName:"ul"},"You can throw ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/error-handling#many-errors-for-one-exception"},"many errors in one exception")," with ",(0,r.kt)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLAggregateException")))),(0,r.kt)("li",{parentName:"ul"},"You can force input types using ",(0,r.kt)("inlineCode",{parentName:"li"},'@UseInputType(for="$id", inputType="ID!")')),(0,r.kt)("li",{parentName:"ul"},"You can extend an input types (just like you could extend an output type in v3) using ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/extend-input-type"},"the new ",(0,r.kt)("inlineCode",{parentName:"a"},"@Decorate")," annotation")),(0,r.kt)("li",{parentName:"ul"},"In a factory, you can ",(0,r.kt)("a",{parentName:"li",href:"input-types#ignoring-some-parameters"},"exclude some optional parameters from the GraphQL schema"))),(0,r.kt)("p",null,"Many extension points have been added"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},'Added a "root type mapper" (useful to map scalar types to PHP types or to add custom annotations related to resolvers)'),(0,r.kt)("li",{parentName:"ul"},"Added ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/field-middlewares"},'"field middlewares"')," (useful to add middleware that modify the way GraphQL fields are handled)"),(0,r.kt)("li",{parentName:"ul"},"Added a ",(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/argument-resolving"},'"parameter type mapper"')," (useful to add customize parameter resolution or add custom annotations related to parameters)")),(0,r.kt)("p",null,"New framework specific features:"),(0,r.kt)("h4",{id:"symfony"},"Symfony:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},'The Symfony bundle now provides a "login" and a "logout" mutation (and also a "me" query)')),(0,r.kt)("h4",{id:"laravel"},"Laravel:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},(0,r.kt)("a",{parentName:"li",href:"/docs/4.3/laravel-package-advanced#support-for-pagination"},"Native integration with the Laravel paginator")," has been added")),(0,r.kt)("h4",{id:"internals"},"Internals:"),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"The ",(0,r.kt)("inlineCode",{parentName:"li"},"FieldsBuilder")," class has been split in many different services (",(0,r.kt)("inlineCode",{parentName:"li"},"FieldsBuilder"),", ",(0,r.kt)("inlineCode",{parentName:"li"},"TypeHandler"),", and a\nchain of ",(0,r.kt)("em",{parentName:"li"},"root type mappers"),")"),(0,r.kt)("li",{parentName:"ul"},"The ",(0,r.kt)("inlineCode",{parentName:"li"},"FieldsBuilderFactory")," class has been completely removed."),(0,r.kt)("li",{parentName:"ul"},"Overall, there is not much in common internally between 4.x and 3.x. 4.x is much more flexible with many more hook points\nthan 3.x. Try it out!")))}c.isMDXComponent=!0}}]);