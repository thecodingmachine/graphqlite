"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[2858],{9365:(e,t,n)=>{n.d(t,{A:()=>u});var a=n(6540),l=n(53);const r={tabItem:"tabItem_Ymn6"};function u(e){let{children:t,hidden:n,className:u}=e;return a.createElement("div",{role:"tabpanel",className:(0,l.A)(r.tabItem,u),hidden:n},t)}},1470:(e,t,n)=>{n.d(t,{A:()=>N});var a=n(8168),l=n(6540),r=n(53),u=n(3104),o=n(6347),i=n(7485),s=n(1682),c=n(9466);function p(e){return function(e){return l.Children.map(e,(e=>{if(!e||(0,l.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:n,attributes:a,default:l}}=e;return{value:t,label:n,attributes:a,default:l}}))}function d(e){const{values:t,children:n}=e;return(0,l.useMemo)((()=>{const e=t??p(n);return function(e){const t=(0,s.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,n])}function m(e){let{value:t,tabValues:n}=e;return n.some((e=>e.value===t))}function g(e){let{queryString:t=!1,groupId:n}=e;const a=(0,o.W6)(),r=function(e){let{queryString:t=!1,groupId:n}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!n)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return n??null}({queryString:t,groupId:n});return[(0,i.aZ)(r),(0,l.useCallback)((e=>{if(!r)return;const t=new URLSearchParams(a.location.search);t.set(r,e),a.replace({...a.location,search:t.toString()})}),[r,a])]}function y(e){const{defaultValue:t,queryString:n=!1,groupId:a}=e,r=d(e),[u,o]=(0,l.useState)((()=>function(e){let{defaultValue:t,tabValues:n}=e;if(0===n.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!m({value:t,tabValues:n}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${n.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const a=n.find((e=>e.default))??n[0];if(!a)throw new Error("Unexpected error: 0 tabValues");return a.value}({defaultValue:t,tabValues:r}))),[i,s]=g({queryString:n,groupId:a}),[p,y]=function(e){let{groupId:t}=e;const n=function(e){return e?`docusaurus.tab.${e}`:null}(t),[a,r]=(0,c.Dv)(n);return[a,(0,l.useCallback)((e=>{n&&r.set(e)}),[n,r])]}({groupId:a}),h=(()=>{const e=i??p;return m({value:e,tabValues:r})?e:null})();(0,l.useLayoutEffect)((()=>{h&&o(h)}),[h]);return{selectedValue:u,selectValue:(0,l.useCallback)((e=>{if(!m({value:e,tabValues:r}))throw new Error(`Can't select invalid tab value=${e}`);o(e),s(e),y(e)}),[s,y,r]),tabValues:r}}var h=n(2303);const f={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:t,block:n,selectedValue:o,selectValue:i,tabValues:s}=e;const c=[],{blockElementScrollPositionUntilNextRender:p}=(0,u.a_)(),d=e=>{const t=e.currentTarget,n=c.indexOf(t),a=s[n].value;a!==o&&(p(t),i(a))},m=e=>{let t=null;switch(e.key){case"Enter":d(e);break;case"ArrowRight":{const n=c.indexOf(e.currentTarget)+1;t=c[n]??c[0];break}case"ArrowLeft":{const n=c.indexOf(e.currentTarget)-1;t=c[n]??c[c.length-1];break}}t?.focus()};return l.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,r.A)("tabs",{"tabs--block":n},t)},s.map((e=>{let{value:t,label:n,attributes:u}=e;return l.createElement("li",(0,a.A)({role:"tab",tabIndex:o===t?0:-1,"aria-selected":o===t,key:t,ref:e=>c.push(e),onKeyDown:m,onClick:d},u,{className:(0,r.A)("tabs__item",f.tabItem,u?.className,{"tabs__item--active":o===t})}),n??t)})))}function v(e){let{lazy:t,children:n,selectedValue:a}=e;const r=(Array.isArray(n)?n:[n]).filter(Boolean);if(t){const e=r.find((e=>e.props.value===a));return e?(0,l.cloneElement)(e,{className:"margin-top--md"}):null}return l.createElement("div",{className:"margin-top--md"},r.map(((e,t)=>(0,l.cloneElement)(e,{key:t,hidden:e.props.value!==a}))))}function q(e){const t=y(e);return l.createElement("div",{className:(0,r.A)("tabs-container",f.tabList)},l.createElement(b,(0,a.A)({},e,t)),l.createElement(v,(0,a.A)({},e,t)))}function N(e){const t=(0,h.A)();return l.createElement(q,(0,a.A)({key:String(t)},e))}},6661:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>c,contentTitle:()=>i,default:()=>g,frontMatter:()=>o,metadata:()=>s,toc:()=>p});var a=n(8168),l=(n(6540),n(5680)),r=(n(7443),n(1470)),u=n(9365);const o={id:"symfony-bundle-advanced",title:"Symfony bundle: advanced usage",sidebar_label:"Symfony specific features"},i=void 0,s={unversionedId:"symfony-bundle-advanced",id:"symfony-bundle-advanced",title:"Symfony bundle: advanced usage",description:"Be advised! This documentation will be removed in a future release.  For current and up-to-date Symfony bundle specific documentation, please see the Github repository.",source:"@site/docs/symfony-bundle-advanced.mdx",sourceDirName:".",slug:"/symfony-bundle-advanced",permalink:"/docs/next/symfony-bundle-advanced",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/symfony-bundle-advanced.mdx",tags:[],version:"current",lastUpdatedBy:"Olexandr Grynchuk",lastUpdatedAt:1710194766,formattedLastUpdatedAt:"Mar 11, 2024",frontMatter:{id:"symfony-bundle-advanced",title:"Symfony bundle: advanced usage",sidebar_label:"Symfony specific features"},sidebar:"docs",previous:{title:"Class with multiple output types",permalink:"/docs/next/multiple-output-types"},next:{title:"Laravel specific features",permalink:"/docs/next/laravel-package-advanced"}},c={},p=[{value:"Login and logout",id:"login-and-logout",level:2},{value:"Schema and request security",id:"schema-and-request-security",level:2},{value:"Login using the &quot;login&quot; mutation",id:"login-using-the-login-mutation",level:3},{value:"Get the current user with the &quot;me&quot; query",id:"get-the-current-user-with-the-me-query",level:3},{value:"Logout using the &quot;logout&quot; mutation",id:"logout-using-the-logout-mutation",level:3},{value:"Injecting the Request",id:"injecting-the-request",level:2}],d={toc:p},m="wrapper";function g(e){let{components:t,...n}=e;return(0,l.yg)(m,(0,a.A)({},d,n,{components:t,mdxType:"MDXLayout"}),(0,l.yg)("div",{class:"alert alert--warning"},(0,l.yg)("strong",null,"Be advised!")," This documentation will be removed in a future release.  For current and up-to-date Symfony bundle specific documentation, please see the ",(0,l.yg)("a",{href:"https://github.com/thecodingmachine/graphqlite-bundle"},"Github repository"),"."),(0,l.yg)("p",null,"The Symfony bundle comes with a number of features to ease the integration of GraphQLite in Symfony."),(0,l.yg)("h2",{id:"login-and-logout"},"Login and logout"),(0,l.yg)("p",null,'Out of the box, the GraphQLite bundle will expose a "login" and a "logout" mutation as well\nas a "me" query (that returns the current user).'),(0,l.yg)("p",null,'If you need to customize this behaviour, you can edit the "graphqlite.security" configuration key.'),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    enable_login: auto # Default setting\n    enable_me: auto # Default setting\n")),(0,l.yg)("p",null,'By default, GraphQLite will enable "login" and "logout" mutations and the "me" query if the following conditions are met:'),(0,l.yg)("ul",null,(0,l.yg)("li",{parentName:"ul"},'the "security" bundle is installed and configured (with a security provider and encoder)'),(0,l.yg)("li",{parentName:"ul"},'the "session" support is enabled (via the "framework.session.enabled" key).')),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    enable_login: on\n")),(0,l.yg)("p",null,"By settings ",(0,l.yg)("inlineCode",{parentName:"p"},"enable_login=on"),", you are stating that you explicitly want the login/logout mutations.\nIf one of the dependencies is missing, an exception is thrown (unlike in default mode where the mutations\nare silently discarded)."),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    enable_login: off\n")),(0,l.yg)("p",null,"Use the ",(0,l.yg)("inlineCode",{parentName:"p"},"enable_login=off")," to disable the mutations."),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    firewall_name: main # default value\n")),(0,l.yg)("p",null,'By default, GraphQLite assumes that your firewall name is "main". This is the default value used in the\nSymfony security bundle so it is likely the value you are using. If for some reason you want to use\nanother firewall, configure the name with ',(0,l.yg)("inlineCode",{parentName:"p"},"graphqlite.security.firewall_name"),"."),(0,l.yg)("h2",{id:"schema-and-request-security"},"Schema and request security"),(0,l.yg)("p",null,"You can disable the introspection of your GraphQL API (for instance in production mode) using\nthe ",(0,l.yg)("inlineCode",{parentName:"p"},"introspection")," configuration properties."),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    introspection: false\n")),(0,l.yg)("p",null,"You can set the maximum complexity and depth of your GraphQL queries using the ",(0,l.yg)("inlineCode",{parentName:"p"},"maximum_query_complexity"),"\nand ",(0,l.yg)("inlineCode",{parentName:"p"},"maximum_query_depth")," configuration properties"),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-yaml"},"graphqlite:\n  security:\n    maximum_query_complexity: 314\n    maximum_query_depth: 42\n")),(0,l.yg)("h3",{id:"login-using-the-login-mutation"},'Login using the "login" mutation'),(0,l.yg)("p",null,"The mutation below will log-in a user:"),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-graphql"},'mutation login {\n  login(userName:"foo", password:"bar") {\n    userName\n    roles\n  }\n}\n')),(0,l.yg)("h3",{id:"get-the-current-user-with-the-me-query"},'Get the current user with the "me" query'),(0,l.yg)("p",null,'Retrieving the current user is easy with the "me" query:'),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n  me {\n    userName\n    roles\n  }\n}\n")),(0,l.yg)("p",null,"In Symfony, user objects implement ",(0,l.yg)("inlineCode",{parentName:"p"},"Symfony\\Component\\Security\\Core\\User\\UserInterface"),".\nThis interface is automatically mapped to a type with 2 fields:"),(0,l.yg)("ul",null,(0,l.yg)("li",{parentName:"ul"},(0,l.yg)("inlineCode",{parentName:"li"},"userName: String!")),(0,l.yg)("li",{parentName:"ul"},(0,l.yg)("inlineCode",{parentName:"li"},"roles: [String!]!"))),(0,l.yg)("p",null,"If you want to get more fields, just add the ",(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," annotation to your user class:"),(0,l.yg)(r.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(u.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass User implements UserInterface\n{\n    #[Field]\n    public function getEmail() : string\n    {\n        // ...\n    }\n\n}\n"))),(0,l.yg)(u.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type\n */\nclass User implements UserInterface\n{\n    /**\n     * @Field\n     */\n    public function getEmail() : string\n    {\n        // ...\n    }\n\n}\n")))),(0,l.yg)("p",null,"You can now query this field using an ",(0,l.yg)("a",{parentName:"p",href:"https://graphql.org/learn/queries/#inline-fragments"},"inline fragment"),":"),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n  me {\n    userName\n    roles\n    ... on User {\n      email\n    }\n  }\n}\n")),(0,l.yg)("h3",{id:"logout-using-the-logout-mutation"},'Logout using the "logout" mutation'),(0,l.yg)("p",null,'Use the "logout" mutation to log a user out'),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-graphql"},"mutation logout {\n  logout\n}\n")),(0,l.yg)("h2",{id:"injecting-the-request"},"Injecting the Request"),(0,l.yg)("p",null,"You can inject the Symfony Request object in any query/mutation/field."),(0,l.yg)("p",null,"Most of the time, getting the request object is irrelevant. Indeed, it is GraphQLite's job to parse this request and\nmanage it for you. Sometimes yet, fetching the request can be needed. In those cases, simply type-hint on the request\nin any parameter of your query/mutation/field."),(0,l.yg)(r.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(u.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"use Symfony\\Component\\HttpFoundation\\Request;\n\n#[Query]\npublic function getUser(int $id, Request $request): User\n{\n    // The $request object contains the Symfony Request.\n}\n"))),(0,l.yg)(u.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"use Symfony\\Component\\HttpFoundation\\Request;\n\n/**\n * @Query\n */\npublic function getUser(int $id, Request $request): User\n{\n    // The $request object contains the Symfony Request.\n}\n")))))}g.isMDXComponent=!0}}]);