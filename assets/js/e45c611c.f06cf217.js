"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[8107],{98697:(e,a,n)=>{n.r(a),n.d(a,{assets:()=>p,contentTitle:()=>l,default:()=>g,frontMatter:()=>r,metadata:()=>o,toc:()=>s});var t=n(58168),i=(n(96540),n(15680));n(67443);const r={id:"laravel-package-advanced",title:"Laravel package: advanced usage",sidebar_label:"Laravel specific features",original_id:"laravel-package-advanced"},l=void 0,o={unversionedId:"laravel-package-advanced",id:"version-4.0/laravel-package-advanced",title:"Laravel package: advanced usage",description:"The Laravel package comes with a number of features to ease the integration of GraphQLite in Laravel.",source:"@site/versioned_docs/version-4.0/laravel-package-advanced.mdx",sourceDirName:".",slug:"/laravel-package-advanced",permalink:"/docs/4.0/laravel-package-advanced",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.0/laravel-package-advanced.mdx",tags:[],version:"4.0",lastUpdatedBy:"Andrey",lastUpdatedAt:1729659602,formattedLastUpdatedAt:"Oct 23, 2024",frontMatter:{id:"laravel-package-advanced",title:"Laravel package: advanced usage",sidebar_label:"Laravel specific features",original_id:"laravel-package-advanced"},sidebar:"version-4.0/docs",previous:{title:"Symfony specific features",permalink:"/docs/4.0/symfony-bundle-advanced"},next:{title:"Internals",permalink:"/docs/4.0/internals"}},p={},s=[{value:"Support for Laravel validation rules",id:"support-for-laravel-validation-rules",level:2},{value:"Support for pagination",id:"support-for-pagination",level:2},{value:"Simple paginator",id:"simple-paginator",level:3},{value:"Using GraphQLite with Eloquent efficiently",id:"using-graphqlite-with-eloquent-efficiently",level:2},{value:"Pitfalls to avoid with Eloquent",id:"pitfalls-to-avoid-with-eloquent",level:3}],d={toc:s},u="wrapper";function g(e){let{components:a,...n}=e;return(0,i.yg)(u,(0,t.A)({},d,n,{components:a,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"The Laravel package comes with a number of features to ease the integration of GraphQLite in Laravel."),(0,i.yg)("h2",{id:"support-for-laravel-validation-rules"},"Support for Laravel validation rules"),(0,i.yg)("p",null,"The GraphQLite Laravel package comes with a special ",(0,i.yg)("inlineCode",{parentName:"p"},"@Validate")," annotation to use Laravel validation rules in your input types."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Laravel\\Annotations\\Validate;\n\nclass MyController\n{\n    /**\n     * @Mutation\n     * @Validate(for="$email", rule="email|unique:users")\n     * @Validate(for="$password", rule="gte:8")\n     */\n    public function createUser(string $email, string $password): User\n    {\n        // ...\n    }\n}\n')),(0,i.yg)("p",null,"You can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"@Validate")," annotation in any query / mutation / field / factory / decorator."),(0,i.yg)("p",null,'If a validation fails to pass, the message will be printed in the "errors" section and you will get a HTTP 400 status code:'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-json"},'{\n    "errors": [\n        {\n            "message": "The email must be a valid email address.",\n            "extensions": {\n                "argument": "email",\n                "category": "Validate"\n            }\n        },\n        {\n            "message": "The password must be greater than or equal 8 characters.",\n            "extensions": {\n                "argument": "password",\n                "category": "Validate"\n            }\n        }\n    ]\n}\n')),(0,i.yg)("p",null,"You can use any validation rule described in ",(0,i.yg)("a",{parentName:"p",href:"https://laravel.com/docs/6.x/validation#available-validation-rules"},"the Laravel documentation")),(0,i.yg)("h2",{id:"support-for-pagination"},"Support for pagination"),(0,i.yg)("p",null,"In your query, if you explicitly return an object that extends  ",(0,i.yg)("inlineCode",{parentName:"p"},"Illuminate\\Pagination\\LengthAwarePaginator"),' class, the query result will be wrapped in a\n"paginator" type.'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class MyController\n{\n    /**\n     * @Query\n     * @return Product[]\n     */\n    public function products(): Illuminate\\Pagination\\LengthAwarePaginator\n    {\n        return Product::paginate(15);\n    }\n}\n")),(0,i.yg)("p",null,"Notice that:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"the method return type MUST BE ",(0,i.yg)("inlineCode",{parentName:"li"},"Illuminate\\Pagination\\LengthAwarePaginator")," or a class extending ",(0,i.yg)("inlineCode",{parentName:"li"},"Illuminate\\Pagination\\LengthAwarePaginator")),(0,i.yg)("li",{parentName:"ul"},"you MUST add a ",(0,i.yg)("inlineCode",{parentName:"li"},"@return")," statement to help GraphQLite find the type of the list")),(0,i.yg)("p",null,"Once this is done, you can get plenty of useful information about this page:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},'products {\n    items {      # The items for the selected page\n        id\n        name\n    }\n    totalCount   # The total count of items.\n    lastPage     # Get the page number of the last available page.\n    firstItem    # Get the "index" of the first item being paginated.\n    lastItem     # Get the "index" of the last item being paginated.\n    hasMorePages # Determine if there are more items in the data source.\n    perPage      # Get the number of items shown per page.\n    hasPages     # Determine if there are enough items to split into multiple pages.\n    currentPage  # Determine the current page being paginated.\n    isEmpty      # Determine if the list of items is empty or not.\n    isNotEmpty   # Determine if the list of items is not empty.\n}\n')),(0,i.yg)("div",{class:"alert alert--warning"},"Be sure to type hint on the class (`Illuminate\\Pagination\\LengthAwarePaginator`) and not on the interface (`Illuminate\\Contracts\\Pagination\\LengthAwarePaginator`). The interface itself is not iterable (it does not extend `Traversable`) and therefore, GraphQLite will refuse to iterate over it."),(0,i.yg)("h3",{id:"simple-paginator"},"Simple paginator"),(0,i.yg)("p",null,"Note: if you are using ",(0,i.yg)("inlineCode",{parentName:"p"},"simplePaginate")," instead of ",(0,i.yg)("inlineCode",{parentName:"p"},"paginate"),", you can type hint on the ",(0,i.yg)("inlineCode",{parentName:"p"},"Illuminate\\Pagination\\Paginator")," class."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class MyController\n{\n    /**\n     * @Query\n     * @return Product[]\n     */\n    public function products(): Illuminate\\Pagination\\Paginator\n    {\n        return Product::simplePaginate(15);\n    }\n}\n")),(0,i.yg)("p",null,"The behaviour will be exactly the same except you will be missing the ",(0,i.yg)("inlineCode",{parentName:"p"},"totalCount")," and ",(0,i.yg)("inlineCode",{parentName:"p"},"lastPage")," fields."),(0,i.yg)("h2",{id:"using-graphqlite-with-eloquent-efficiently"},"Using GraphQLite with Eloquent efficiently"),(0,i.yg)("p",null,"In GraphQLite, you are supposed to put a ",(0,i.yg)("inlineCode",{parentName:"p"},"@Field")," annotation on each getter."),(0,i.yg)("p",null,"Eloquent uses PHP magic properties to expose your database records.\nBecause Eloquent relies on magic properties, it is quite rare for an Eloquent model to have proper getters and setters."),(0,i.yg)("p",null,"So we need to find a workaround. GraphQLite comes with a ",(0,i.yg)("inlineCode",{parentName:"p"},"@MagicField")," annotation to help you\nworking with magic properties."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type()\n * @MagicField(name="id" outputType="ID!")\n * @MagicField(name="name" phpType="string")\n * @MagicField(name="categories" phpType="Category[]")\n */\nclass Product extends Model\n{\n}\n')),(0,i.yg)("p",null,'Please note that since the properties are "magic", they don\'t have a type. Therefore,\nyou need to pass either the "outputType" attribute with the GraphQL type matching the property,\nor the "phpType" attribute with the PHP type matching the property.'),(0,i.yg)("h3",{id:"pitfalls-to-avoid-with-eloquent"},"Pitfalls to avoid with Eloquent"),(0,i.yg)("p",null,"When designing relationships in Eloquent, you write a method to expose that relationship this way:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class User extends Model\n{\n    /**\n     * Get the phone record associated with the user.\n     */\n    public function phone()\n    {\n        return $this->hasOne('App\\Phone');\n    }\n}\n")),(0,i.yg)("p",null,"It would be tempting to put a ",(0,i.yg)("inlineCode",{parentName:"p"},"@Field")," annotation on the ",(0,i.yg)("inlineCode",{parentName:"p"},"phone()")," method, but this will not work. Indeed,\nthe ",(0,i.yg)("inlineCode",{parentName:"p"},"phone()")," method does not return a ",(0,i.yg)("inlineCode",{parentName:"p"},"App\\Phone")," object. It is the ",(0,i.yg)("inlineCode",{parentName:"p"},"phone")," magic property that returns it."),(0,i.yg)("p",null,"In short:"),(0,i.yg)("div",{class:"alert alert--danger"},"This does not work:",(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},"```php\nclass User extends Model\n{\n    /**\n     * @Field\n     */\n    public function phone()\n    {\n        return $this->hasOne('App\\Phone');\n    }\n}\n```\n"))),(0,i.yg)("div",{class:"alert alert--success"},"This works:",(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},'```php\n/**\n* @MagicField(name="phone", phpType="App\\\\Phone")\n*/\nclass User extends Model\n{\n    public function phone()\n    {\n        return $this->hasOne(\'App\\Phone\');\n    }\n}\n```\n'))))}g.isMDXComponent=!0}}]);