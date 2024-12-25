"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[3642],{19365:(e,t,n)=>{n.d(t,{A:()=>o});var a=n(96540),r=n(20053);const i={tabItem:"tabItem_Ymn6"};function o(e){let{children:t,hidden:n,className:o}=e;return a.createElement("div",{role:"tabpanel",className:(0,r.A)(i.tabItem,o),hidden:n},t)}},11470:(e,t,n)=>{n.d(t,{A:()=>w});var a=n(58168),r=n(96540),i=n(20053),o=n(23104),s=n(56347),u=n(57485),l=n(31682),c=n(89466);function d(e){return function(e){return r.Children.map(e,(e=>{if(!e||(0,r.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:n,attributes:a,default:r}}=e;return{value:t,label:n,attributes:a,default:r}}))}function h(e){const{values:t,children:n}=e;return(0,r.useMemo)((()=>{const e=t??d(n);return function(e){const t=(0,l.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,n])}function g(e){let{value:t,tabValues:n}=e;return n.some((e=>e.value===t))}function p(e){let{queryString:t=!1,groupId:n}=e;const a=(0,s.W6)(),i=function(e){let{queryString:t=!1,groupId:n}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!n)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return n??null}({queryString:t,groupId:n});return[(0,u.aZ)(i),(0,r.useCallback)((e=>{if(!i)return;const t=new URLSearchParams(a.location.search);t.set(i,e),a.replace({...a.location,search:t.toString()})}),[i,a])]}function m(e){const{defaultValue:t,queryString:n=!1,groupId:a}=e,i=h(e),[o,s]=(0,r.useState)((()=>function(e){let{defaultValue:t,tabValues:n}=e;if(0===n.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!g({value:t,tabValues:n}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${n.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const a=n.find((e=>e.default))??n[0];if(!a)throw new Error("Unexpected error: 0 tabValues");return a.value}({defaultValue:t,tabValues:i}))),[u,l]=p({queryString:n,groupId:a}),[d,m]=function(e){let{groupId:t}=e;const n=function(e){return e?`docusaurus.tab.${e}`:null}(t),[a,i]=(0,c.Dv)(n);return[a,(0,r.useCallback)((e=>{n&&i.set(e)}),[n,i])]}({groupId:a}),y=(()=>{const e=u??d;return g({value:e,tabValues:i})?e:null})();(0,r.useLayoutEffect)((()=>{y&&s(y)}),[y]);return{selectedValue:o,selectValue:(0,r.useCallback)((e=>{if(!g({value:e,tabValues:i}))throw new Error(`Can't select invalid tab value=${e}`);s(e),l(e),m(e)}),[l,m,i]),tabValues:i}}var y=n(92303);const f={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:t,block:n,selectedValue:s,selectValue:u,tabValues:l}=e;const c=[],{blockElementScrollPositionUntilNextRender:d}=(0,o.a_)(),h=e=>{const t=e.currentTarget,n=c.indexOf(t),a=l[n].value;a!==s&&(d(t),u(a))},g=e=>{let t=null;switch(e.key){case"Enter":h(e);break;case"ArrowRight":{const n=c.indexOf(e.currentTarget)+1;t=c[n]??c[0];break}case"ArrowLeft":{const n=c.indexOf(e.currentTarget)-1;t=c[n]??c[c.length-1];break}}t?.focus()};return r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,i.A)("tabs",{"tabs--block":n},t)},l.map((e=>{let{value:t,label:n,attributes:o}=e;return r.createElement("li",(0,a.A)({role:"tab",tabIndex:s===t?0:-1,"aria-selected":s===t,key:t,ref:e=>c.push(e),onKeyDown:g,onClick:h},o,{className:(0,i.A)("tabs__item",f.tabItem,o?.className,{"tabs__item--active":s===t})}),n??t)})))}function v(e){let{lazy:t,children:n,selectedValue:a}=e;const i=(Array.isArray(n)?n:[n]).filter(Boolean);if(t){const e=i.find((e=>e.props.value===a));return e?(0,r.cloneElement)(e,{className:"margin-top--md"}):null}return r.createElement("div",{className:"margin-top--md"},i.map(((e,t)=>(0,r.cloneElement)(e,{key:t,hidden:e.props.value!==a}))))}function N(e){const t=m(e);return r.createElement("div",{className:(0,i.A)("tabs-container",f.tabList)},r.createElement(b,(0,a.A)({},e,t)),r.createElement(v,(0,a.A)({},e,t)))}function w(e){const t=(0,y.A)();return r.createElement(N,(0,a.A)({key:String(t)},e))}},55123:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>u,contentTitle:()=>o,default:()=>h,frontMatter:()=>i,metadata:()=>s,toc:()=>l});var a=n(58168),r=(n(96540),n(15680));n(67443),n(11470),n(19365);const i={id:"authentication-authorization",title:"Authentication and authorization",sidebar_label:"Authentication and authorization"},o=void 0,s={unversionedId:"authentication-authorization",id:"version-6.1/authentication-authorization",title:"Authentication and authorization",description:"You might not want to expose your GraphQL API to anyone. Or you might want to keep some queries/mutations or fields",source:"@site/versioned_docs/version-6.1/authentication-authorization.mdx",sourceDirName:".",slug:"/authentication-authorization",permalink:"/docs/6.1/authentication-authorization",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-6.1/authentication-authorization.mdx",tags:[],version:"6.1",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1735156155,formattedLastUpdatedAt:"Dec 25, 2024",frontMatter:{id:"authentication-authorization",title:"Authentication and authorization",sidebar_label:"Authentication and authorization"},sidebar:"docs",previous:{title:"User input validation",permalink:"/docs/6.1/validation"},next:{title:"Fine grained security",permalink:"/docs/6.1/fine-grained-security"}},u={},l=[{value:"<code>@Logged</code> and <code>@Right</code> annotations",id:"logged-and-right-annotations",level:2},{value:"Not throwing errors",id:"not-throwing-errors",level:2},{value:"Injecting the current user as a parameter",id:"injecting-the-current-user-as-a-parameter",level:2},{value:"Hiding fields / queries / mutations",id:"hiding-fields--queries--mutations",level:2}],c={toc:l},d="wrapper";function h(e){let{components:t,...n}=e;return(0,r.yg)(d,(0,a.A)({},c,n,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("p",null,"You might not want to expose your GraphQL API to anyone. Or you might want to keep some queries/mutations or fields\nreserved to some users."),(0,r.yg)("p",null,"GraphQLite offers some control over what a user can do with your API. You can restrict access to resources:"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"based on authentication using the ",(0,r.yg)("a",{parentName:"li",href:"#logged-and-right-annotations"},(0,r.yg)("inlineCode",{parentName:"a"},"@Logged")," annotation")," (restrict access to logged users)"),(0,r.yg)("li",{parentName:"ul"},"based on authorization using the ",(0,r.yg)("a",{parentName:"li",href:"#logged-and-right-annotations"},(0,r.yg)("inlineCode",{parentName:"a"},"@Right")," annotation")," (restrict access to logged users with certain rights)."),(0,r.yg)("li",{parentName:"ul"},"based on fine-grained authorization using the ",(0,r.yg)("a",{parentName:"li",href:"/docs/6.1/fine-grained-security"},(0,r.yg)("inlineCode",{parentName:"a"},"@Security")," annotation")," (restrict access for some given resources to some users).")),(0,r.yg)("div",{class:"alert alert--info"},"GraphQLite does not have its own security mechanism. Unless you're using our Symfony Bundle or our Laravel package, it is up to you to connect this feature to your framework's security mechanism.",(0,r.yg)("br",null),"See ",(0,r.yg)("a",{href:"implementing-security"},"Connecting GraphQLite to your framework's security module"),"."),(0,r.yg)("h2",{id:"logged-and-right-annotations"},(0,r.yg)("inlineCode",{parentName:"h2"},"@Logged")," and ",(0,r.yg)("inlineCode",{parentName:"h2"},"@Right")," annotations"),(0,r.yg)("p",null,"GraphQLite exposes two annotations (",(0,r.yg)("inlineCode",{parentName:"p"},"@Logged")," and ",(0,r.yg)("inlineCode",{parentName:"p"},"@Right"),") that you can use to restrict access to a resource."),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Logged;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Right;\n\nclass UserController\n{\n    /**\n     * @return User[]\n     */\n    #[Query]\n    #[Logged]\n    #[Right("CAN_VIEW_USER_LIST")]\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,r.yg)("p",null,"In the example above, the query ",(0,r.yg)("inlineCode",{parentName:"p"},"users")," will only be available if the user making the query is logged AND if he\nhas the ",(0,r.yg)("inlineCode",{parentName:"p"},"CAN_VIEW_USER_LIST")," right."),(0,r.yg)("p",null,(0,r.yg)("inlineCode",{parentName:"p"},"@Logged")," and ",(0,r.yg)("inlineCode",{parentName:"p"},"@Right")," annotations can be used next to:"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"@Query")," annotations"),(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"@Mutation")," annotations"),(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"@Field")," annotations")),(0,r.yg)("div",{class:"alert alert--info"},"By default, if a user tries to access an unauthorized query/mutation/field, an error is raised and the query fails."),(0,r.yg)("h2",{id:"not-throwing-errors"},"Not throwing errors"),(0,r.yg)("p",null,"If you do not want an error to be thrown when a user attempts to query a field/query/mutation he has no access to, you can use the ",(0,r.yg)("inlineCode",{parentName:"p"},"@FailWith")," annotation."),(0,r.yg)("p",null,"The ",(0,r.yg)("inlineCode",{parentName:"p"},"@FailWith")," annotation contains the value that will be returned for users with insufficient rights."),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'class UserController\n{\n    /**\n     * If a user is not logged or if the user has not the right "CAN_VIEW_USER_LIST",\n     * the value returned will be "null".\n     *\n     * @return User[]\n     */\n    #[Query]\n    #[Logged]\n    #[Right("CAN_VIEW_USER_LIST")]\n    #[FailWith(value: null)]\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,r.yg)("h2",{id:"injecting-the-current-user-as-a-parameter"},"Injecting the current user as a parameter"),(0,r.yg)("p",null,"Use the ",(0,r.yg)("inlineCode",{parentName:"p"},"@InjectUser")," annotation to get an instance of the current user logged in."),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\nuse TheCodingMachine\\GraphQLite\\Annotations\\InjectUser;\n\nclass ProductController\n{\n    /**\n     * @Query\n     * @return Product\n     */\n    public function product(\n            int $id,\n            #[InjectUser]\n            User $user\n        ): Product\n    {\n        // ...\n    }\n}\n")),(0,r.yg)("p",null,"The ",(0,r.yg)("inlineCode",{parentName:"p"},"@InjectUser")," annotation can be used next to:"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"@Query")," annotations"),(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"@Mutation")," annotations"),(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"@Field")," annotations")),(0,r.yg)("p",null,"The object injected as the current user depends on your framework. It is in fact the object returned by the\n",(0,r.yg)("a",{parentName:"p",href:"/docs/6.1/implementing-security"},'"authentication service" configured in GraphQLite'),"."),(0,r.yg)("h2",{id:"hiding-fields--queries--mutations"},"Hiding fields / queries / mutations"),(0,r.yg)("p",null,"By default, a user analysing the GraphQL schema can see all queries/mutations/types available.\nSome will be available to him and some won't."),(0,r.yg)("p",null,"If you want to add an extra level of security (or if you want your schema to be kept secret to unauthorized users),\nyou can use the ",(0,r.yg)("inlineCode",{parentName:"p"},"@HideIfUnauthorized")," annotation."),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'class UserController\n{\n    /**\n     * If a user is not logged or if the user has not the right "CAN_VIEW_USER_LIST",\n     * the schema will NOT contain the "users" query at all (so trying to call the\n     * "users" query will result in a GraphQL "query not found" error.\n     *\n     * @return User[]\n     */\n    #[Query]\n    #[Logged]\n    #[Right("CAN_VIEW_USER_LIST")]\n    #[HideIfUnauthorized]\n    public function users(int $limit, int $offset): array\n    {\n        // ...\n    }\n}\n')),(0,r.yg)("p",null,"While this is the most secured mode, it can have drawbacks when working with development tools\n(you need to be logged as admin to fetch the complete schema)."),(0,r.yg)("div",{class:"alert alert--info"},'The "HideIfUnauthorized" mode was the default mode in GraphQLite 3 and is optionnal from GraphQLite 4+.'))}h.isMDXComponent=!0}}]);