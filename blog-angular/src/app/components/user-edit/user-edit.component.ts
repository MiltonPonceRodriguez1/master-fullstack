import { Component, OnInit } from '@angular/core';
import { User } from '../../models/user';
import { UserService } from '../../services/user.service';
import { global } from '../../services/global';

@Component({
  selector: 'app-user-edit',
  templateUrl: './user-edit.component.html',
  styleUrls: ['./user-edit.component.css'],
  providers: [UserService]
})
export class UserEditComponent implements OnInit {
  public page_title: string;
  public user: User;
  public identity;
  public token;
  public status;
  public url;
  public afuConfig = {
    multiple: false,
    formatsAllowed: ".jpg,.png,.gif,.jpeg",
    maxSize: "50",
    uploadAPI:  {
      url: global.url+'user/upload',
      method:"POST",
      headers: {
        "Authorization": this._userService.getToken()
      }
    },
    theme: "attachPin",
    hideProgressBar: false,
    hideResetBtn: true,
    hideSelectBtn: false,
    attachPinText: 'Sube tu avatar de usuario'  
  };

  constructor(
    private _userService: UserService
  ) { 
    this.page_title = 'Ajustes de usuario';
    this.user = new User(1, '', '', 'ROLE_USER', '', '', '', '');
    this.identity = this._userService.getIdentity();
    this.token = this._userService.getToken();
    this.url = global.url;

    // RELLENAR OBJETO USUARIO.
    this.user = new User(this.identity.sub, 
                        this.identity.name,
                        this.identity.surname,
                        this.identity.role,
                        this.identity.email, '',
                        this.identity.description,
                        this.identity.image,
    );
  }

  ngOnInit(): void {
  }

  onSubmit(form) {
    this._userService.update(this.token, this.user).subscribe(
      response => {
        if (response && response.status) {
          console.log(response);
          this.status = 'success';

          // ACTUALIZAR USUARIO EN SESION
          if (response.changes.name) {
            this.identity.name = response.changes.name;
          }

          if (response.changes.surname) {
            this.identity.surname = response.changes.surname;
          }

          if (response.changes.email) {
            this.identity.email = response.changes.email;
          }

          if (response.changes.description) {
            this.identity.description = response.changes.description;
          }

          if (response.changes.image) {
            this.identity.image = response.changes.image;
          }

          console.log(this.identity);
          localStorage.setItem('identity', JSON.stringify(this.identity));
        } else {
          this.status = 'error';
        }
      },
      error => {
        this.status = 'error';
        console.log(<any>error);
      }
    );
  }

  avatarUpload(datos) {
    let data = JSON.parse(datos.response);
    this.user.image = data.image;
  }

}
