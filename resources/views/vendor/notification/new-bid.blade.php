<div style="background-color: #f2f2f2; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 3px 15px rgba(0,0,0,0.2);">
      <h1 style="color: #333; margin-bottom: 20px;"><span>Hurry up </span>{{ $user->name }}</h1>
      <img src="https://img.freepik.com/vecteurs-libre/bulle-dialogue-texte-wow-style-pop-art_603843-1001.jpg?t=st=1680545896~exp=1680546496~hmac=271b0457044f4035fb1f30c8863915664e6789178dcb4bdcff284b5d542a7447" style="max-width: 100%; height: auto; display: block; margin: 0 auto; margin-bottom: 20px;">
      <p style="font-size: 18px; line-height: 1.5;">Someone just make a new bid on the <b>{{ $auction->nft->title }}</b> item</p>
      <p style="font-size: 18px; line-height: 1.5;"><span>To see this item, <a href="<?= env("URL_CLIENT") . '/auction/' . $auction->id ?>">check out here</a></span></p>
    </div>
  </div>
</div>
</div>