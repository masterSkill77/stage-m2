<div style="background-color: #f2f2f2; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 3px 15px rgba(0,0,0,0.2);">
      <h1 style="color: #333; margin-bottom: 20px;"><span>Congratulations </span>{{ $user->name }}</h1>
      <img src="https://img.freepik.com/vecteurs-libre/concept-papier-peint-confettis-realiste_23-2148444771.jpg" style="max-width: 100%; height: auto; display: block; margin: 0 auto; margin-bottom: 20px;">
      <p style="font-size: 18px; line-height: 1.5;">You just won the <b>{{ $auction->nft->title }}</b> item</p>
      <p style="font-size: 18px; line-height: 1.5;"><span>To see your newly item, <a href="<?= env("URL_CLIENT") . '/nft/' . $auction->nft->id ?>">check out here</a></span></p>
    </div>
  </div>
</div>
</div>