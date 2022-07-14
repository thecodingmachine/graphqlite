"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[9780],{3905:(e,t,n)=>{n.d(t,{Zo:()=>u,kt:()=>d});var r=n(67294);function a(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function o(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function s(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?o(Object(n),!0).forEach((function(t){a(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):o(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function l(e,t){if(null==e)return{};var n,r,a=function(e,t){if(null==e)return{};var n,r,a={},o=Object.keys(e);for(r=0;r<o.length;r++)n=o[r],t.indexOf(n)>=0||(a[n]=e[n]);return a}(e,t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(r=0;r<o.length;r++)n=o[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(a[n]=e[n])}return a}var i=r.createContext({}),p=function(e){var t=r.useContext(i),n=t;return e&&(n="function"==typeof e?e(t):s(s({},t),e)),n},u=function(e){var t=p(e.components);return r.createElement(i.Provider,{value:t},e.children)},c={inlineCode:"code",wrapper:function(e){var t=e.children;return r.createElement(r.Fragment,{},t)}},h=r.forwardRef((function(e,t){var n=e.components,a=e.mdxType,o=e.originalType,i=e.parentName,u=l(e,["components","mdxType","originalType","parentName"]),h=p(n),d=a,m=h["".concat(i,".").concat(d)]||h[d]||c[d]||o;return n?r.createElement(m,s(s({ref:t},u),{},{components:n})):r.createElement(m,s({ref:t},u))}));function d(e,t){var n=arguments,a=t&&t.mdxType;if("string"==typeof e||a){var o=n.length,s=new Array(o);s[0]=h;var l={};for(var i in t)hasOwnProperty.call(t,i)&&(l[i]=t[i]);l.originalType=e,l.mdxType="string"==typeof e?e:a,s[1]=l;for(var p=2;p<o;p++)s[p]=n[p];return r.createElement.apply(null,s)}return r.createElement.apply(null,n)}h.displayName="MDXCreateElement"},85162:(e,t,n)=>{n.d(t,{Z:()=>s});var r=n(67294),a=n(86010);const o="tabItem_Ymn6";function s(e){let{children:t,hidden:n,className:s}=e;return r.createElement("div",{role:"tabpanel",className:(0,a.Z)(o,s),hidden:n},t)}},65488:(e,t,n)=>{n.d(t,{Z:()=>d});var r=n(87462),a=n(67294),o=n(86010),s=n(72389),l=n(67392),i=n(7094),p=n(12466);const u="tabList__CuJ",c="tabItem_LNqP";function h(e){var t,n;const{lazy:s,block:h,defaultValue:d,values:m,groupId:f,className:b}=e,y=a.Children.map(e.children,(e=>{if((0,a.isValidElement)(e)&&"value"in e.props)return e;throw new Error("Docusaurus error: Bad <Tabs> child <"+("string"==typeof e.type?e.type:e.type.name)+'>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.')})),v=null!=m?m:y.map((e=>{let{props:{value:t,label:n,attributes:r}}=e;return{value:t,label:n,attributes:r}})),g=(0,l.l)(v,((e,t)=>e.value===t.value));if(g.length>0)throw new Error('Docusaurus error: Duplicate values "'+g.map((e=>e.value)).join(", ")+'" found in <Tabs>. Every value needs to be unique.');const T=null===d?d:null!=(t=null!=d?d:null==(n=y.find((e=>e.props.default)))?void 0:n.props.value)?t:y[0].props.value;if(null!==T&&!v.some((e=>e.value===T)))throw new Error('Docusaurus error: The <Tabs> has a defaultValue "'+T+'" but none of its children has the corresponding value. Available values are: '+v.map((e=>e.value)).join(", ")+". If you intend to show no default tab, use defaultValue={null} instead.");const{tabGroupChoices:k,setTabGroupChoices:w}=(0,i.U)(),[P,N]=(0,a.useState)(T),x=[],{blockElementScrollPositionUntilNextRender:O}=(0,p.o5)();if(null!=f){const e=k[f];null!=e&&e!==P&&v.some((t=>t.value===e))&&N(e)}const $=e=>{const t=e.currentTarget,n=x.indexOf(t),r=v[n].value;r!==P&&(O(t),N(r),null!=f&&w(f,String(r)))},U=e=>{var t;let n=null;switch(e.key){case"ArrowRight":{var r;const t=x.indexOf(e.currentTarget)+1;n=null!=(r=x[t])?r:x[0];break}case"ArrowLeft":{var a;const t=x.indexOf(e.currentTarget)-1;n=null!=(a=x[t])?a:x[x.length-1];break}}null==(t=n)||t.focus()};return a.createElement("div",{className:(0,o.Z)("tabs-container",u)},a.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,o.Z)("tabs",{"tabs--block":h},b)},v.map((e=>{let{value:t,label:n,attributes:s}=e;return a.createElement("li",(0,r.Z)({role:"tab",tabIndex:P===t?0:-1,"aria-selected":P===t,key:t,ref:e=>x.push(e),onKeyDown:U,onFocus:$,onClick:$},s,{className:(0,o.Z)("tabs__item",c,null==s?void 0:s.className,{"tabs__item--active":P===t})}),null!=n?n:t)}))),s?(0,a.cloneElement)(y.filter((e=>e.props.value===P))[0],{className:"margin-top--md"}):a.createElement("div",{className:"margin-top--md"},y.map(((e,t)=>(0,a.cloneElement)(e,{key:t,hidden:e.props.value!==P})))))}function d(e){const t=(0,s.Z)();return a.createElement(h,(0,r.Z)({key:String(t)},e))}},42938:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>u,contentTitle:()=>i,default:()=>d,frontMatter:()=>l,metadata:()=>p,toc:()=>c});var r=n(87462),a=(n(67294),n(3905)),o=n(65488),s=n(85162);const l={id:"prefetch-method",title:"Prefetching records",sidebar_label:"Prefetching records"},i=void 0,p={unversionedId:"prefetch-method",id:"version-5.0/prefetch-method",title:"Prefetching records",description:"The problem",source:"@site/versioned_docs/version-5.0/prefetch-method.mdx",sourceDirName:".",slug:"/prefetch-method",permalink:"/docs/5.0/prefetch-method",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-5.0/prefetch-method.mdx",tags:[],version:"5.0",lastUpdatedBy:"bladl",lastUpdatedAt:1657811576,formattedLastUpdatedAt:"7/14/2022",frontMatter:{id:"prefetch-method",title:"Prefetching records",sidebar_label:"Prefetching records"},sidebar:"version-5.0/docs",previous:{title:"Query plan",permalink:"/docs/5.0/query-plan"},next:{title:"File uploads",permalink:"/docs/5.0/file-uploads"}},u={},c=[{value:"The problem",id:"the-problem",level:2},{value:"The &quot;prefetch&quot; method",id:"the-prefetch-method",level:2},{value:"Input arguments",id:"input-arguments",level:2}],h={toc:c};function d(e){let{components:t,...n}=e;return(0,a.kt)("wrapper",(0,r.Z)({},h,n,{components:t,mdxType:"MDXLayout"}),(0,a.kt)("h2",{id:"the-problem"},"The problem"),(0,a.kt)("p",null,'GraphQL naive implementations often suffer from the "N+1" problem.'),(0,a.kt)("p",null,"Consider a request where a user attached to a post must be returned:"),(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-graphql"},"{\n    posts {\n        id\n        user {\n            id\n        }\n    }\n}\n")),(0,a.kt)("p",null,"A naive implementation will do this:"),(0,a.kt)("ul",null,(0,a.kt)("li",{parentName:"ul"},"1 query to fetch the list of posts"),(0,a.kt)("li",{parentName:"ul"},"1 query per post to fetch the user")),(0,a.kt)("p",null,'Assuming we have "N" posts, we will make "N+1" queries.'),(0,a.kt)("p",null,'There are several ways to fix this problem.\nAssuming you are using a relational database, one solution is to try to look\nahead and perform only one query with a JOIN between "posts" and "users".\nThis method is described in the ',(0,a.kt)("a",{parentName:"p",href:"/docs/5.0/query-plan"},'"analyzing the query plan" documentation'),"."),(0,a.kt)("p",null,"But this can be difficult to implement. This is also only useful for relational databases. If your data comes from a\nNoSQL database or from the cache, this will not help."),(0,a.kt)("p",null,"Instead, GraphQLite offers an easier to implement solution: the ability to fetch all fields from a given type at once."),(0,a.kt)("h2",{id:"the-prefetch-method"},'The "prefetch" method'),(0,a.kt)(o.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,a.kt)(s.Z,{value:"php8",mdxType:"TabItem"},(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-php"},'#[Type]\nclass PostType {\n    /**\n     * @param Post $post\n     * @param mixed $prefetchedUsers\n     * @return User\n     */\n    #[Field(prefetchMethod: "prefetchUsers")]\n    public function getUser(Post $post, $prefetchedUsers): User\n    {\n        // This method will receive the $prefetchedUsers as second argument. This is the return value of the "prefetchUsers" method below.\n        // Using this prefetched list, it should be easy to map it to the post\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchUsers(iterable $posts)\n    {\n        // This function is called only once per GraphQL request\n        // with the list of posts. You can fetch the list of users\n        // associated with this posts in a single request,\n        // for instance using a "IN" query in SQL or a multi-fetch\n        // in your cache back-end.\n    }\n}\n'))),(0,a.kt)(s.Z,{value:"php7",mdxType:"TabItem"},(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type\n */\nclass PostType {\n    /**\n     * @Field(prefetchMethod="prefetchUsers")\n     * @param Post $post\n     * @param mixed $prefetchedUsers\n     * @return User\n     */\n    public function getUser(Post $post, $prefetchedUsers): User\n    {\n        // This method will receive the $prefetchedUsers as second argument. This is the return value of the "prefetchUsers" method below.\n        // Using this prefetched list, it should be easy to map it to the post\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchUsers(iterable $posts)\n    {\n        // This function is called only once per GraphQL request\n        // with the list of posts. You can fetch the list of users\n        // associated with this posts in a single request,\n        // for instance using a "IN" query in SQL or a multi-fetch\n        // in your cache back-end.\n    }\n}\n')))),(0,a.kt)("p",null,'When the "prefetchMethod" attribute is detected in the "@Field" annotation, the method is called automatically.\nThe first argument of the method is an array of instances of the main type.\nThe "prefetchMethod" can return absolutely anything (mixed). The return value will be passed as the second parameter of the "@Field" annotated method.'),(0,a.kt)("h2",{id:"input-arguments"},"Input arguments"),(0,a.kt)("p",null,"Field arguments can be set either on the @Field annotated method OR/AND on the prefetchMethod."),(0,a.kt)("p",null,"For instance:"),(0,a.kt)(o.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,a.kt)(s.Z,{value:"php8",mdxType:"TabItem"},(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-php"},'#[Type]\nclass PostType {\n    /**\n     * @param Post $post\n     * @param mixed $prefetchedComments\n     * @return Comment[]\n     */\n    #[Field(prefetchMethod: "prefetchComments")]\n    public function getComments(Post $post, $prefetchedComments): array\n    {\n        // ...\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchComments(iterable $posts, bool $hideSpam, int $filterByScore)\n    {\n        // Parameters passed after the first parameter (hideSpam, filterByScore...) are automatically exposed\n        // as GraphQL arguments for the "comments" field.\n    }\n}\n'))),(0,a.kt)(s.Z,{value:"php7",mdxType:"TabItem"},(0,a.kt)("pre",null,(0,a.kt)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type\n */\nclass PostType {\n    /**\n     * @Field(prefetchMethod="prefetchComments")\n     * @param Post $post\n     * @param mixed $prefetchedComments\n     * @return Comment[]\n     */\n    public function getComments(Post $post, $prefetchedComments): array\n    {\n        // ...\n    }\n\n    /**\n     * @param Post[] $posts\n     * @return mixed\n     */\n    public function prefetchComments(iterable $posts, bool $hideSpam, int $filterByScore)\n    {\n        // Parameters passed after the first parameter (hideSpam, filterByScore...) are automatically exposed\n        // as GraphQL arguments for the "comments" field.\n    }\n}\n')))),(0,a.kt)("p",null,"The prefetch method MUST be in the same class as the @Field-annotated method and MUST be public."))}d.isMDXComponent=!0}}]);