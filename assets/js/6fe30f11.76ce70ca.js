"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5235],{9365:(e,t,n)=>{n.d(t,{A:()=>l});var a=n(6540),r=n(53);const s={tabItem:"tabItem_Ymn6"};function l(e){let{children:t,hidden:n,className:l}=e;return a.createElement("div",{role:"tabpanel",className:(0,r.A)(s.tabItem,l),hidden:n},t)}},1470:(e,t,n)=>{n.d(t,{A:()=>w});var a=n(8168),r=n(6540),s=n(53),l=n(3104),o=n(6347),i=n(7485),u=n(1682),c=n(9466);function p(e){return function(e){return r.Children.map(e,(e=>{if(!e||(0,r.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:n,attributes:a,default:r}}=e;return{value:t,label:n,attributes:a,default:r}}))}function h(e){const{values:t,children:n}=e;return(0,r.useMemo)((()=>{const e=t??p(n);return function(e){const t=(0,u.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,n])}function d(e){let{value:t,tabValues:n}=e;return n.some((e=>e.value===t))}function m(e){let{queryString:t=!1,groupId:n}=e;const a=(0,o.W6)(),s=function(e){let{queryString:t=!1,groupId:n}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!n)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return n??null}({queryString:t,groupId:n});return[(0,i.aZ)(s),(0,r.useCallback)((e=>{if(!s)return;const t=new URLSearchParams(a.location.search);t.set(s,e),a.replace({...a.location,search:t.toString()})}),[s,a])]}function f(e){const{defaultValue:t,queryString:n=!1,groupId:a}=e,s=h(e),[l,o]=(0,r.useState)((()=>function(e){let{defaultValue:t,tabValues:n}=e;if(0===n.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!d({value:t,tabValues:n}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${n.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const a=n.find((e=>e.default))??n[0];if(!a)throw new Error("Unexpected error: 0 tabValues");return a.value}({defaultValue:t,tabValues:s}))),[i,u]=m({queryString:n,groupId:a}),[p,f]=function(e){let{groupId:t}=e;const n=function(e){return e?`docusaurus.tab.${e}`:null}(t),[a,s]=(0,c.Dv)(n);return[a,(0,r.useCallback)((e=>{n&&s.set(e)}),[n,s])]}({groupId:a}),b=(()=>{const e=i??p;return d({value:e,tabValues:s})?e:null})();(0,r.useLayoutEffect)((()=>{b&&o(b)}),[b]);return{selectedValue:l,selectValue:(0,r.useCallback)((e=>{if(!d({value:e,tabValues:s}))throw new Error(`Can't select invalid tab value=${e}`);o(e),u(e),f(e)}),[u,f,s]),tabValues:s}}var b=n(2303);const y={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function g(e){let{className:t,block:n,selectedValue:o,selectValue:i,tabValues:u}=e;const c=[],{blockElementScrollPositionUntilNextRender:p}=(0,l.a_)(),h=e=>{const t=e.currentTarget,n=c.indexOf(t),a=u[n].value;a!==o&&(p(t),i(a))},d=e=>{let t=null;switch(e.key){case"Enter":h(e);break;case"ArrowRight":{const n=c.indexOf(e.currentTarget)+1;t=c[n]??c[0];break}case"ArrowLeft":{const n=c.indexOf(e.currentTarget)-1;t=c[n]??c[c.length-1];break}}t?.focus()};return r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,s.A)("tabs",{"tabs--block":n},t)},u.map((e=>{let{value:t,label:n,attributes:l}=e;return r.createElement("li",(0,a.A)({role:"tab",tabIndex:o===t?0:-1,"aria-selected":o===t,key:t,ref:e=>c.push(e),onKeyDown:d,onClick:h},l,{className:(0,s.A)("tabs__item",y.tabItem,l?.className,{"tabs__item--active":o===t})}),n??t)})))}function v(e){let{lazy:t,children:n,selectedValue:a}=e;const s=(Array.isArray(n)?n:[n]).filter(Boolean);if(t){const e=s.find((e=>e.props.value===a));return e?(0,r.cloneElement)(e,{className:"margin-top--md"}):null}return r.createElement("div",{className:"margin-top--md"},s.map(((e,t)=>(0,r.cloneElement)(e,{key:t,hidden:e.props.value!==a}))))}function T(e){const t=f(e);return r.createElement("div",{className:(0,s.A)("tabs-container",y.tabList)},r.createElement(g,(0,a.A)({},e,t)),r.createElement(v,(0,a.A)({},e,t)))}function w(e){const t=(0,b.A)();return r.createElement(T,(0,a.A)({key:String(t)},e))}},488:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>c,contentTitle:()=>i,default:()=>m,frontMatter:()=>o,metadata:()=>u,toc:()=>p});var a=n(8168),r=(n(6540),n(5680)),s=(n(7443),n(1470)),l=n(9365);const o={id:"prefetch-method",title:"Prefetching records",sidebar_label:"Prefetching records"},i=void 0,u={unversionedId:"prefetch-method",id:"version-3.0/prefetch-method",title:"Prefetching records",description:"The problem",source:"@site/versioned_docs/version-3.0/prefetch_method.mdx",sourceDirName:".",slug:"/prefetch-method",permalink:"/docs/3.0/prefetch-method",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/prefetch_method.mdx",tags:[],version:"3.0",lastUpdatedBy:"Olexandr Grynchuk",lastUpdatedAt:1710194766,formattedLastUpdatedAt:"Mar 11, 2024",frontMatter:{id:"prefetch-method",title:"Prefetching records",sidebar_label:"Prefetching records"}},c={},p=[{value:"The problem",id:"the-problem",level:2},{value:"The &quot;prefetch&quot; method",id:"the-prefetch-method",level:2},{value:"Input arguments",id:"input-arguments",level:2}],h={toc:p},d="wrapper";function m(e){let{components:t,...n}=e;return(0,r.yg)(d,(0,a.A)({},h,n,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("h2",{id:"the-problem"},"The problem"),(0,r.yg)("p",null,'GraphQL naive implementations often suffer from the "N+1" problem.'),(0,r.yg)("p",null,"Consider a request where a user attached to a post must be returned:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-graphql"},"{\n    posts {\n        id\n        user {\n            id\n        }\n    }\n}\n")),(0,r.yg)("p",null,"A naive implementation will do this:"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"1 query to fetch the list of posts"),(0,r.yg)("li",{parentName:"ul"},"1 query per post to fetch the user")),(0,r.yg)("p",null,'Assuming we have "N" posts, we will make "N+1" queries.'),(0,r.yg)("p",null,'There are several ways to fix this problem.\nAssuming you are using a relational database, one solution is to try to look\nahead and perform only one query with a JOIN between "posts" and "users".\nThis method is described in the ',(0,r.yg)("a",{parentName:"p",href:"/docs/3.0/query-plan"},'"analyzing the query plan" documentation'),"."),(0,r.yg)("p",null,"But this can be difficult to implement. This is also only useful for relational databases. If your data comes from a\nNoSQL database or from the cache, this will not help."),(0,r.yg)("p",null,"Instead, GraphQLite offers an easier to implement solution: the ability to fetch all fields from a given type at once."),(0,r.yg)("h2",{id:"the-prefetch-method"},'The "prefetch" method'),(0,r.yg)(s.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.yg)(l.A,{value:"php8",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'#[Type]\nclass PostType {\n    /**\n     * @param mixed $prefetchedUsers\n     * @return User\n     */\n    #[Field(prefetchMethod: "prefetchUsers")]\n    public function getUser($prefetchedUsers): User\n    {\n        // This method will receive the $prefetchedUsers as second argument. This is the return value of the "prefetchUsers" method below.\n        // Using this prefetched list, it should be easy to map it to the post\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchUsers(iterable $posts)\n    {\n        // This function is called only once per GraphQL request\n        // with the list of posts. You can fetch the list of users\n        // associated with this posts in a single request,\n        // for instance using a "IN" query in SQL or a multi-fetch\n        // in your cache back-end.\n    }\n}\n'))),(0,r.yg)(l.A,{value:"php7",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type\n */\nclass PostType {\n    /**\n     * @Field(prefetchMethod="prefetchUsers")\n     * @param mixed $prefetchedUsers\n     * @return User\n     */\n    public function getUser($prefetchedUsers): User\n    {\n        // This method will receive the $prefetchedUsers as second argument. This is the return value of the "prefetchUsers" method below.\n        // Using this prefetched list, it should be easy to map it to the post\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchUsers(iterable $posts)\n    {\n        // This function is called only once per GraphQL request\n        // with the list of posts. You can fetch the list of users\n        // associated with this posts in a single request,\n        // for instance using a "IN" query in SQL or a multi-fetch\n        // in your cache back-end.\n    }\n}\n')))),(0,r.yg)("p",null,'When the "prefetchMethod" attribute is detected in the "@Field" annotation, the method is called automatically.\nThe first argument of the method is an array of instances of the main type.\nThe "prefetchMethod" can return absolutely anything (mixed). The return value will be passed as the second parameter of the "@Field" annotated method.'),(0,r.yg)("h2",{id:"input-arguments"},"Input arguments"),(0,r.yg)("p",null,"Field arguments can be set either on the @Field annotated method OR/AND on the prefetchMethod."),(0,r.yg)("p",null,"For instance:"),(0,r.yg)(s.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.yg)(l.A,{value:"php8",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'#[Type]\nclass PostType {\n    /**\n     * @param mixed $prefetchedComments\n     * @return Comment[]\n     */\n    #[Field(prefetchMethod: "prefetchComments")]\n    public function getComments($prefetchedComments): array\n    {\n        // ...\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchComments(iterable $posts, bool $hideSpam, int $filterByScore)\n    {\n        // Parameters passed after the first parameter (hideSpam, filterByScore...) are automatically exposed\n        // as GraphQL arguments for the "comments" field.\n    }\n}\n'))),(0,r.yg)(l.A,{value:"php7",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type\n */\nclass PostType {\n    /**\n     * @Field(prefetchMethod="prefetchComments")\n     * @param mixed $prefetchedComments\n     * @return Comment[]\n     */\n    public function getComments($prefetchedComments): array\n    {\n        // ...\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchComments(iterable $posts, bool $hideSpam, int $filterByScore)\n    {\n        // Parameters passed after the first parameter (hideSpam, filterByScore...) are automatically exposed\n        // as GraphQL arguments for the "comments" field.\n    }\n}\n')))),(0,r.yg)("p",null,"The prefetch method MUST be in the same class as the @Field-annotated method and MUST be public."))}m.isMDXComponent=!0}}]);