<div style="background-color: #f2f2f2; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 3px 15px rgba(0,0,0,0.2);">
      <h1 style="color: #333; margin-bottom: 20px;"><span>Welcome </span>{{ $user->username }}</h1>
      <img src="https://img.freepik.com/vecteurs-libre/heureux-petit-peuple-pres-enorme-illustration-plate-mot-bienvenue_74855-10808.jpg?w=740&t=st=1680799126~exp=1680799726~hmac=3939d9ed54a48383887bb409fa59f4660dc31346bf0f9426e50ac5f61708a21d" style="max-width: 100%; height: auto; display: block; margin: 0 auto; margin-bottom: 20px;">
      <p style="font-size: 18px; line-height: 1.5;">Please, verify your email on this url  <a href="<?= env("APP_URL") . '/api/verify/'. $user->id .'/' . $token ?>">here</a></span></p>
    </div>
  </div>
</div>
</div>