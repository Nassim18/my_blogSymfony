# My_SymfonyBlog
A blog created by **BOUALI Mohammed-Amin** and **SHOUT Oussama Nassim** as a part of a symfony project of the teaching unit **E-Application**.

## Heroku and login credentials : 
- In order to access our blog on **Heroku** platform, please follow this link :
	 [https://my-blogsymfony.herokuapp.com/](https://my-blogsymfony.herokuapp.com/)
- To connect as Administrator you can use the credentials bellow : 
`username : Admin` 
 `password : azerty`
## Functionalities and security 
- A visitor of the blog has the ability to read posts, register/login to our website, and contact the development team. (credentials of our mail account bellow) 
- In order to register to our blog, user must provide a username, e-mail address (which we verify if the two of them already exists on server side), and a password (must contain at least 6 characters) 
- A simple user of our blog has the ability to :
  - Publish a new post.
  - Comment on a post.
  - Delete his own comments.
  - Delete and edit his own posts. 
  - And he has also the ability to do what a visitor can do.
- A user with administrator privileges can :
  - Delete and edit any post.
  - Delete any comment.
  - Delete users.
  - And he has also the ability to do what a simple user can do.   
- In order to edit or add a post when you are connected, please go to My Profile page, then go to the Post Managment section.
- We've added some restrictions on our blog routes, in order to limit the access to each root by user role (visitor, simple user, admin)
> Dev team mail credentials :
>  mail address : blogsymfony.dev@gmail.com
>  password : symf1234



## Used bundles :
```webpackencorebundle``` : render all of the dynamic `script` and `link`.
```SensioFrameworkExtraBundle``` : add annotations for controllers.
```Mailer``` : to get the contact us works with mail sending.
```security-bundle``` : manage access and user login processes.
```classDeclaration.getMethods() ``` : returns a *MethodDeclaration[]* containing the class method declarations regardless of whether it's an instance of static method.
