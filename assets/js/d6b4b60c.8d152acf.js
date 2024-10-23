"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[6556],{48204:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>g,contentTitle:()=>i,default:()=>m,frontMatter:()=>r,metadata:()=>o,toc:()=>p});var n=a(58168),l=(a(96540),a(15680));a(67443);const r={id:"annotations_reference",title:"Annotations reference",sidebar_label:"Annotations reference",original_id:"annotations_reference"},i=void 0,o={unversionedId:"annotations_reference",id:"version-4.0/annotations_reference",title:"Annotations reference",description:"@Query annotation",source:"@site/versioned_docs/version-4.0/annotations_reference.md",sourceDirName:".",slug:"/annotations_reference",permalink:"/docs/4.0/annotations_reference",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.0/annotations_reference.md",tags:[],version:"4.0",lastUpdatedBy:"Andrey",lastUpdatedAt:1729659602,formattedLastUpdatedAt:"Oct 23, 2024",frontMatter:{id:"annotations_reference",title:"Annotations reference",sidebar_label:"Annotations reference",original_id:"annotations_reference"},sidebar:"version-4.0/docs",previous:{title:"Migrating",permalink:"/docs/4.0/migrating"},next:{title:"Semantic versioning",permalink:"/docs/4.0/semver"}},g={},p=[{value:"@Query annotation",id:"query-annotation",level:2},{value:"@Mutation annotation",id:"mutation-annotation",level:2},{value:"@Type annotation",id:"type-annotation",level:2},{value:"@ExtendType annotation",id:"extendtype-annotation",level:2},{value:"@Field annotation",id:"field-annotation",level:2},{value:"@SourceField annotation",id:"sourcefield-annotation",level:2},{value:"@MagicField annotation",id:"magicfield-annotation",level:2},{value:"@Logged annotation",id:"logged-annotation",level:2},{value:"@Right annotation",id:"right-annotation",level:2},{value:"@FailWith annotation",id:"failwith-annotation",level:2},{value:"@HideIfUnauthorized annotation",id:"hideifunauthorized-annotation",level:2},{value:"@InjectUser annotation",id:"injectuser-annotation",level:2},{value:"@Security annotation",id:"security-annotation",level:2},{value:"@Factory annotation",id:"factory-annotation",level:2},{value:"@UseInputType annotation",id:"useinputtype-annotation",level:2},{value:"@Decorate annotation",id:"decorate-annotation",level:2},{value:"@Autowire annotation",id:"autowire-annotation",level:2},{value:"@HideParameter annotation",id:"hideparameter-annotation",level:2},{value:"@Validate annotation",id:"validate-annotation",level:2},{value:"@Assertion annotation",id:"assertion-annotation",level:2},{value:"@EnumType annotation",id:"enumtype-annotation",level:2}],y={toc:p},d="wrapper";function m(e){let{components:t,...a}=e;return(0,l.yg)(d,(0,n.A)({},y,a,{components:t,mdxType:"MDXLayout"}),(0,l.yg)("h2",{id:"query-annotation"},"@Query annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query")," annotation is used to declare a GraphQL query."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": controller methods."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the query. If skipped, the name of the method is used instead.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/custom-types"},"outputType")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"Forces the GraphQL output type of a query.")))),(0,l.yg)("h2",{id:"mutation-annotation"},"@Mutation annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," annotation is used to declare a GraphQL mutation."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": controller methods."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the mutation. If skipped, the name of the method is used instead.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/custom-types"},"outputType")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"Forces the GraphQL output type of a query.")))),(0,l.yg)("h2",{id:"type-annotation"},"@Type annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," annotation is used to declare a GraphQL object type."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": classes."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"class"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},'The targeted class. If no class is passed, the type applies to the current class. The current class is assumed to be an entity. If the "class" attribute is passed, ',(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/external_type_declaration"},"the class annotated with ",(0,l.yg)("inlineCode",{parentName:"a"},"@Type")," is a service"),".")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},'The name of the GraphQL type generated. If not passed, the name of the class is used. If the class ends with "Type", the "Type" suffix is removed')),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"default"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"bool"),(0,l.yg)("td",{parentName:"tr",align:null},"Defaults to ",(0,l.yg)("em",{parentName:"td"},"true"),". Whether the targeted PHP class should be mapped by default to this type.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"external"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"bool"),(0,l.yg)("td",{parentName:"tr",align:null},"Whether this is an ",(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/external_type_declaration"},"external type declaration"),' or not. You usually do not need to use this attribute since this value defaults to true if a "class" attribute is set. This is only useful if you are declaring a type with no PHP class mapping using the "name" attribute.')))),(0,l.yg)("h2",{id:"extendtype-annotation"},"@ExtendType annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType")," annotation is used to add fields to an existing GraphQL object type."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": classes."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"class"),(0,l.yg)("td",{parentName:"tr",align:null},"see below"),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The targeted class. ",(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/extend_type"},"The class annotated with ",(0,l.yg)("inlineCode",{parentName:"a"},"@ExtendType")," is a service"),".")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},"see below"),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The targeted GraphQL output type.")))),(0,l.yg)("p",null,'One and only one of "class" and "name" parameter can be passed at the same time.'),(0,l.yg)("h2",{id:"field-annotation"},"@Field annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," annotation is used to declare a GraphQL field."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods of classes annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType"),"."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the field. If skipped, the name of the method is used instead.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/type_mapping"},"outputType")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"Forces the GraphQL output type of a query.")))),(0,l.yg)("h2",{id:"sourcefield-annotation"},"@SourceField annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@SourceField")," annotation is used to declare a GraphQL field."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": classes annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType"),"."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the field.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/type_mapping"},"outputType")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"Forces the GraphQL output type of the field. Otherwise, return type is used.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"phpType"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The PHP type of the field (as you would write it in a Docblock)")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"annotations"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"array\\<Annotations",">"),(0,l.yg)("td",{parentName:"tr",align:null},'A set of annotations that apply to this field. You would typically used a "@Logged" or "@Right" annotation here.')))),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Note"),": ",(0,l.yg)("inlineCode",{parentName:"p"},"outputType")," and ",(0,l.yg)("inlineCode",{parentName:"p"},"phpType")," are mutually exclusive."),(0,l.yg)("h2",{id:"magicfield-annotation"},"@MagicField annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@MagicField")," annotation is used to declare a GraphQL field that originates from a PHP magic property (using ",(0,l.yg)("inlineCode",{parentName:"p"},"__get")," magic method)."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": classes annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType"),"."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the field.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("a",{parentName:"td",href:"/docs/4.0/type_mapping"},"outputType")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no"),"(*)"),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The GraphQL output type of the field.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"phpType"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no"),"(*)"),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The PHP type of the field (as you would write it in a Docblock)")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"annotations"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"array\\<Annotations",">"),(0,l.yg)("td",{parentName:"tr",align:null},'A set of annotations that apply to this field. You would typically used a "@Logged" or "@Right" annotation here.')))),(0,l.yg)("p",null,"(*) ",(0,l.yg)("strong",{parentName:"p"},"Note"),": ",(0,l.yg)("inlineCode",{parentName:"p"},"outputType")," and ",(0,l.yg)("inlineCode",{parentName:"p"},"phpType")," are mutually exclusive. You MUST provide one of them."),(0,l.yg)("h2",{id:"logged-annotation"},"@Logged annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Logged")," annotation is used to declare a Query/Mutation/Field is only visible to logged users."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field"),"."),(0,l.yg)("p",null,"This annotation allows no attributes."),(0,l.yg)("h2",{id:"right-annotation"},"@Right annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Right")," annotation is used to declare a Query/Mutation/Field is only visible to users with a specific right."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field"),"."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the right.")))),(0,l.yg)("h2",{id:"failwith-annotation"},"@FailWith annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@FailWith")," annotation is used to declare a default value to return in the user is not authorized to see a specific\nquery / mutation / field (according to the ",(0,l.yg)("inlineCode",{parentName:"p"},"@Logged")," and ",(0,l.yg)("inlineCode",{parentName:"p"},"@Right")," annotations)."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," and one of ",(0,l.yg)("inlineCode",{parentName:"p"},"@Logged")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Right")," annotations."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"default")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"mixed"),(0,l.yg)("td",{parentName:"tr",align:null},"The value to return if the user is not authorized.")))),(0,l.yg)("h2",{id:"hideifunauthorized-annotation"},"@HideIfUnauthorized annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@HideIfUnauthorized")," annotation is used to completely hide the query / mutation / field if the user is not authorized\nto access it (according to the ",(0,l.yg)("inlineCode",{parentName:"p"},"@Logged")," and ",(0,l.yg)("inlineCode",{parentName:"p"},"@Right")," annotations)."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," and one of ",(0,l.yg)("inlineCode",{parentName:"p"},"@Logged")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Right")," annotations."),(0,l.yg)("p",null,(0,l.yg)("inlineCode",{parentName:"p"},"@HideIfUnauthorized")," and ",(0,l.yg)("inlineCode",{parentName:"p"},"@FailWith")," are mutually exclusive."),(0,l.yg)("h2",{id:"injectuser-annotation"},"@InjectUser annotation"),(0,l.yg)("p",null,"Use the ",(0,l.yg)("inlineCode",{parentName:"p"},"@InjectUser")," annotation to inject an instance of the current user logged in into a parameter of your\nquery / mutation / field."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field"),"."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"for")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the PHP parameter")))),(0,l.yg)("h2",{id:"security-annotation"},"@Security annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Security")," annotation can be used to check fin-grained access rights.\nIt is very flexible: it allows you to pass an expression that can contains custom logic."),(0,l.yg)("p",null,"See ",(0,l.yg)("a",{parentName:"p",href:"/docs/4.0/fine-grained-security"},"the fine grained security page")," for more details."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field"),"."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"default")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The security expression")))),(0,l.yg)("h2",{id:"factory-annotation"},"@Factory annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Factory")," annotation is used to declare a factory that turns GraphQL input types into objects."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),': methods from classes in the "types" namespace.'),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the input type. If skipped, the name of class returned by the factory is used instead.")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"default"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"bool"),(0,l.yg)("td",{parentName:"tr",align:null},"If ",(0,l.yg)("inlineCode",{parentName:"td"},"true"),", this factory will be used by default for its PHP return type. If set to ",(0,l.yg)("inlineCode",{parentName:"td"},"false"),", you must explicitly ",(0,l.yg)("a",{parentName:"td",href:"http://localhost:3000/docs/input-types#declaring-several-input-types-for-the-same-php-class"},"reference this factory using the ",(0,l.yg)("inlineCode",{parentName:"a"},"@Parameter")," annotation"),".")))),(0,l.yg)("h2",{id:"useinputtype-annotation"},"@UseInputType annotation"),(0,l.yg)("p",null,"Used to override the GraphQL input type of a PHP parameter."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," annotation."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"for")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the PHP parameter")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"inputType")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The GraphQL input type to force for this input field")))),(0,l.yg)("h2",{id:"decorate-annotation"},"@Decorate annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Decorate")," annotation is used ",(0,l.yg)("a",{parentName:"p",href:"/docs/4.0/extend_input_type"},"to extend/modify/decorate an input type declared with the ",(0,l.yg)("inlineCode",{parentName:"a"},"@Factory")," annotation"),"."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),': methods from classes in the "types" namespace.'),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The GraphQL input type name extended by this decorator.")))),(0,l.yg)("h2",{id:"autowire-annotation"},"@Autowire annotation"),(0,l.yg)("p",null,(0,l.yg)("a",{parentName:"p",href:"/docs/4.0/autowiring"},"Resolves a PHP parameter from the container"),"."),(0,l.yg)("p",null,"Useful to inject services directly into ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," method arguments."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," annotation."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"for")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the PHP parameter")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"identifier")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},'The identifier of the service to fetch. This is optional. Please avoid using this attribute as this leads to a "service locator" anti-pattern.')))),(0,l.yg)("h2",{id:"hideparameter-annotation"},"@HideParameter annotation"),(0,l.yg)("p",null,"Removes ",(0,l.yg)("a",{parentName:"p",href:"/docs/4.0/input-types#ignoring-some-parameters"},"an argument from the GraphQL schema"),"."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"for")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the PHP parameter to hide")))),(0,l.yg)("h2",{id:"validate-annotation"},"@Validate annotation"),(0,l.yg)("div",{class:"alert alert--info"},"This annotation is only available in the GraphQLite Laravel package"),(0,l.yg)("p",null,(0,l.yg)("a",{parentName:"p",href:"/docs/4.0/laravel-package-advanced"},"Validates a user input in Laravel"),"."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Factory")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Decorator")," annotation."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"for")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the PHP parameter")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"rule")),(0,l.yg)("td",{parentName:"tr",align:null},"*yes"),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"Laravel validation rules")))),(0,l.yg)("p",null,"Sample:"),(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre"},'@Validate(for="$email", rule="email|unique:users")\n')),(0,l.yg)("h2",{id:"assertion-annotation"},"@Assertion annotation"),(0,l.yg)("p",null,(0,l.yg)("a",{parentName:"p",href:"/docs/4.0/validation"},"Validates a user input"),"."),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@Assertion")," annotation  is available in the ",(0,l.yg)("em",{parentName:"p"},"thecodingmachine/graphqlite-symfony-validator-bridge")," third party package.\nIt is available out of the box if you use the Symfony bundle."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": methods annotated with ",(0,l.yg)("inlineCode",{parentName:"p"},"@Query"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Mutation"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Field"),", ",(0,l.yg)("inlineCode",{parentName:"p"},"@Factory")," or ",(0,l.yg)("inlineCode",{parentName:"p"},"@Decorator")," annotation."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"for")),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"yes")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the PHP parameter")),(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"constraint")),(0,l.yg)("td",{parentName:"tr",align:null},"*yes"),(0,l.yg)("td",{parentName:"tr",align:null},"annotation"),(0,l.yg)("td",{parentName:"tr",align:null},"One (or many) Symfony validation annotations.")))),(0,l.yg)("h2",{id:"enumtype-annotation"},"@EnumType annotation"),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"@EnumType"),' annotation is used to change the name of a "Enum" type.\nNote that if you do not want to change the name, the annotation is optionnal. Any object extending ',(0,l.yg)("inlineCode",{parentName:"p"},"MyCLabs\\Enum\\Enum"),"\nis automatically mapped to a GraphQL enum type."),(0,l.yg)("p",null,(0,l.yg)("strong",{parentName:"p"},"Applies on"),": classes extending the ",(0,l.yg)("inlineCode",{parentName:"p"},"MyCLabs\\Enum\\Enum")," base class."),(0,l.yg)("table",null,(0,l.yg)("thead",{parentName:"table"},(0,l.yg)("tr",{parentName:"thead"},(0,l.yg)("th",{parentName:"tr",align:null},"Attribute"),(0,l.yg)("th",{parentName:"tr",align:null},"Compulsory"),(0,l.yg)("th",{parentName:"tr",align:null},"Type"),(0,l.yg)("th",{parentName:"tr",align:null},"Definition"))),(0,l.yg)("tbody",{parentName:"table"},(0,l.yg)("tr",{parentName:"tbody"},(0,l.yg)("td",{parentName:"tr",align:null},"name"),(0,l.yg)("td",{parentName:"tr",align:null},(0,l.yg)("em",{parentName:"td"},"no")),(0,l.yg)("td",{parentName:"tr",align:null},"string"),(0,l.yg)("td",{parentName:"tr",align:null},"The name of the enum type (in the GraphQL schema)")))))}m.isMDXComponent=!0}}]);