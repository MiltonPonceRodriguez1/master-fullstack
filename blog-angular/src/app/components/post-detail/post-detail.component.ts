import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { PostService } from '../../services/post.service';
import { UserService } from '../../services/user.service';
import { Post } from '../../models/post';
import { global } from '../../services/global'; 

@Component({
  selector: 'app-post-detail',
  templateUrl: './post-detail.component.html',
  styleUrls: ['./post-detail.component.css'],
  providers: [PostService, UserService]
})
export class PostDetailComponent implements OnInit {
  public post: Post;
  public identity;
  public token;
  public url;

  constructor(
    private _route: ActivatedRoute,
    private _router: Router,
    private _postService: PostService,
    private _userService: UserService
  ) { 
    this.url = global.url;
    this.identity = this._userService.getIdentity();
    this.token = this._userService.getToken();
  }

  ngOnInit(): void {
    this.getPost();
  }

  getPost() {
    // OBTENER EL ID DEL POST DESDE LA URL.
    this._route.params.subscribe(params => {
      let id = +params['id'];
      
      // PETICION AJAX PARA SACAR LOS DATOS DEL POST.
      this._postService.getPost(id).subscribe(
        response => {
          if (response.status == 'success') {            
            this.post = response.post;
            console.log(this.post);
          } else {
            this._router.navigate(['inicio']);
          }
        }, 
        error => {
          console.log(error);
          this._router.navigate(['inicio']);
        }
      );
    });    
  }

}
