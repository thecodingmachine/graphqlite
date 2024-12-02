"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[2112],{52660:(e,t,r)=>{r.r(t),r.d(t,{assets:()=>l,contentTitle:()=>o,default:()=>h,frontMatter:()=>n,metadata:()=>u,toc:()=>d});var i=r(58168),s=(r(96540),r(15680)),a=r(67443);const n={id:"automatic-persisted-queries",title:"Automatic persisted queries",sidebar_label:"Automatic persisted queries"},o=void 0,u={unversionedId:"automatic-persisted-queries",id:"automatic-persisted-queries",title:"Automatic persisted queries",description:"The problem",source:"@site/docs/automatic-persisted-queries.mdx",sourceDirName:".",slug:"/automatic-persisted-queries",permalink:"/docs/next/automatic-persisted-queries",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/automatic-persisted-queries.mdx",tags:[],version:"current",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1733174531,formattedLastUpdatedAt:"Dec 2, 2024",frontMatter:{id:"automatic-persisted-queries",title:"Automatic persisted queries",sidebar_label:"Automatic persisted queries"},sidebar:"docs",previous:{title:"Prefetching records",permalink:"/docs/next/prefetch-method"},next:{title:"File uploads",permalink:"/docs/next/file-uploads"}},l={},d=[{value:"The problem",id:"the-problem",level:2},{value:"Apollo APQ",id:"apollo-apq",level:2},{value:"Setup",id:"setup",level:2}],p={toc:d},c="wrapper";function h(e){let{components:t,...r}=e;return(0,s.yg)(c,(0,i.A)({},p,r,{components:t,mdxType:"MDXLayout"}),(0,s.yg)("h2",{id:"the-problem"},"The problem"),(0,s.yg)("p",null,"Clients send queries to GraphQLite as HTTP requests that include the GraphQL string of the query to execute.\nDepending on your graph's schema, the size of a valid query string might be arbitrarily large.\nAs query strings become larger, increased latency and network usage can noticeably degrade client performance."),(0,s.yg)("p",null,'To combat this, GraphQL servers use a technique called "persisted queries". The basic idea is instead of\nsending the whole query string, clients only send it\'s unique identifier. The server then finds the actual\nquery string by given identifier and use that as if the client sent the whole query in the first place.\nThat helps improve GraphQL network performance with zero build-time configuration by sending smaller GraphQL HTTP requests.\nA smaller request payload reduces bandwidth utilization and speeds up GraphQL Client loading times.'),(0,s.yg)("h2",{id:"apollo-apq"},"Apollo APQ"),(0,s.yg)("p",null,(0,s.yg)("a",{parentName:"p",href:"https://www.apollographql.com/docs/apollo-server/performance/apq/"},"Automatic persisted queries (APQ) is technique created by Apollo"),"\nand is aimed to implement a simple automatic way of persisting queries. Queries are cached on the server side,\nalong with its unique identifier (always its SHA-256 hash). Clients can send this identifier instead of the\ncorresponding query string, thus reducing request sizes dramatically (response sizes are unaffected)."),(0,s.yg)("p",null,"To persist a query string, GraphQLite server must first receive it from a requesting client.\nConsequently, each unique query string must be sent to Apollo Server at least once.\nAfter any client sends a query string to persist, every client that executes that query can then benefit from APQ."),(0,s.yg)(a.K,{chart:"sequenceDiagram;\n  Client app->>GraphQL Server: Sends SHA-256 hash of query string to execute\n  Note over GraphQL Server: Fails to find persisted query string\n  GraphQL Server->>Client app: Responds with error\n  Client app->>GraphQL Server: Sends both query string AND hash\n  Note over GraphQL Server: Persists query string and hash\n  GraphQL Server->>Client app: Executes query and returns result\n  Note over Client app: Time passes\n  Client app->>GraphQL Server: Sends SHA-256 hash of query string to execute\n  Note over GraphQL Server: Finds persisted query string\n  GraphQL Server->>Client app: Executes query and returns result",mdxType:"Mermaid"}),(0,s.yg)("p",null,"Persisted queries are especially effective when clients send queries as GET requests.\nThis enables clients to take advantage of the browser cache and integrate with a CDN."),(0,s.yg)("p",null,"Because query identifiers are deterministic hashes, clients can generate them at runtime. No additional build steps are required."),(0,s.yg)("h2",{id:"setup"},"Setup"),(0,s.yg)("p",null,"To use Automatic persisted queries with GraphQLite, you may use\n",(0,s.yg)("inlineCode",{parentName:"p"},"useAutomaticPersistedQueries")," method when building your PSR-15 middleware:"),(0,s.yg)("pre",null,(0,s.yg)("code",{parentName:"pre",className:"language-php"},"$builder = new Psr15GraphQLMiddlewareBuilder($schema);\n\n// You need to provide a PSR compatible cache and a TTL for queries. The best cache would be some kind\n// of in-memory cache with a limit on number of entries to make sure your cache can't be maliciously spammed with queries.\n$builder->useAutomaticPersistedQueries($cache, new DateInterval('PT1H'));\n")))}h.isMDXComponent=!0}}]);