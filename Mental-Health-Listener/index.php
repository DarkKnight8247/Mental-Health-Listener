<?php
$convo_dir = __DIR__ . '/conversations';

if (!is_dir($convo_dir)) {
mkdir($convo_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
header('Content-Type: application/json');
$ajax_type = $_POST['ajax'];

if ($ajax_type === '1') {
  $userQuestion = strtolower(trim($_POST['question'] ?? ''));
  $lang = $_POST['lang'] ?? 'en';

$responses_en = [
    ['keywords' => ['hi', 'hello', 'hey'], 'response' => "Hello buddy! I'm always here to chat. You can talk to me about anything on your mind — no matter how big or small. Let's connect."],
    ['keywords' => ['sad', 'depressed', 'unhappy'], 'response' => "I'm really sorry you're feeling sad. It can be heavy to carry those feelings alone. You can share what's weighing on you, and I'll listen without any judgment."],
    ['keywords' => ['happy', 'joy', 'glad'], 'response' => "That's wonderful to hear! Embrace that joy and celebrate even the small wins. What made you feel this way?"],
    ['keywords' => ['help', 'support', 'assist'], 'response' => "I'm here to help however I can. You don't have to go through this alone. Tell me what's on your mind, and we can work through it together."],
    ['keywords' => ['anxious', 'nervous', 'worried'], 'response' => "It's completely okay to feel that way. Deep breaths can help. Want to talk more about what's making you anxious? I'm here to listen."],
    ['keywords' => ['alone', 'lonely', 'isolated'], 'response' => "You might feel alone, but I'm here to keep you company. Let's chat about what you're experiencing. You matter to me."],
    ['keywords' => ['panic', 'scared', 'fear'], 'response' => "Take slow, deep breaths. You are safe right now. I'm here with you, and we can take it one moment at a time."],
    ['keywords' => ['tired', 'exhausted', 'burnout'], 'response' => "You've been pushing through so much. It's okay to rest. You deserve a break, and I'm here to remind you of that."],
    ['keywords' => ['cry', 'tears', 'weeping'], 'response' => "Crying is a natural way to release pent-up emotions. Let it out — I'm still here for you, ready to listen whenever you're ready to talk."],
    ['keywords' => ['worthless', 'useless', 'hopeless'], 'response' => "You may feel that way, but you are far from worthless. Your feelings are valid, and your presence matters more than you know."],
    ['keywords' => ['numb', 'empty', 'blank'], 'response' => "Feeling numb can be unsettling, but it doesn't mean you're alone. Let's talk through it step by step. I'm here to listen."],
    ['keywords' => ['give up', 'quit', 'done'], 'response' => "Please hold on. Even the darkest nights end with a new dawn. Let's talk through what's making you feel this way."],
    ['keywords' => ['why', 'confused', 'lost'], 'response' => "It's okay to feel confused or lost. Life can be overwhelming sometimes. Let's explore what's weighing on your mind together."],
    ['keywords' => ['need', 'talk', 'someone'], 'response' => "You don't have to face this alone. I'm here for you, ready to listen without any judgment. What's going on?"],
    ['keywords' => ['hate', 'myself', 'self'], 'response' => "I'm really sorry you're feeling this way. You deserve kindness — even from yourself. Let's talk about what's making you feel this way."],
    ['keywords' => ['sleep', 'insomnia', 'awake'], 'response' => "Sleep troubles can be tough. Want to talk about what's keeping you up? Maybe getting it off your chest can help."],
    ['keywords' => ['understand', 'ignored', 'alone'], 'response' => "It can feel that way sometimes, but you are seen and heard. I'm here to listen without judgment, whenever you're ready to talk."],
    ['keywords' => ['broken', 'shattered', 'crushed'], 'response' => "You may feel broken, but you're still whole. I'm here to talk it through with you. Let's take it one step at a time."],
    ['keywords' => ['lost', 'confused', 'uncertain'], 'response' => "Feeling lost is tough, but you don't have to navigate it alone. Let's find some direction together. What are you feeling unsure about?"],
    ['keywords' => ['do not know', 'unsure', 'unclear'], 'response' => "Let's figure it out together, step by step."],
    ['keywords' => ['can i', 'share', 'secret'], 'response' => "Of course. I'm here for anything you want to share."],
    ['keywords' => ['trapped', 'stuck', 'closed'], 'response' => "That sounds heavy. Want to share what's making you feel that way?"],
    ['keywords' => ['nothing', 'flat', 'emotionless'], 'response' => "Emotional numbness can be tough. You're still valid in your experience."],
    ['keywords' => ['wrong', 'broken', 'flawed'], 'response' => "There's nothing wrong with feeling things deeply. Your'e human."],
    ['keywords' => ['tired', 'lazy', 'sleepy'], 'response' => "Mental stress can exhaust you. You're not lazy—you're overwhelmed."],
    ['keywords' => ['scared', 'afraid', 'fear'], 'response' => "It's okay to be scared. You're safe here with me."],
    ['keywords' => ['overthink', 'thoughts', 'racing'], 'response' => "Overthinking is exhausting. Want to unload your thoughts here?"],
    ['keywords' => ['normal', 'crazy', 'weird'], 'response' => "What you're feeling is valid. Many go through the same."],
    ['keywords' => ['better', 'heal', 'fix'], 'response' => "Healing is a process, not a race. Let's take it slow together."],
    ['keywords' => ['not okay', 'unwell', 'down'], 'response' => "It's brave to admit that. I'm proud of you. Let's talk more."],
    ['keywords' => ['guilty', 'shame', 'regret'], 'response' => "Guilt can weigh a lot. Want to talk about what's causing it?"],
    ['keywords' => ['overwhelmed', 'stress', 'pressure'], 'response' => "Take a breath. One thing at a time. I'm right here with you."],
    ['keywords' => ['cope', 'handle', 'deal'], 'response' => "Everyone copes differently. Want to explore what might work for you?"],
    ['keywords' => ['break', 'rest', 'pause'], 'response' => "Then take one. You're allowed to pause."],
    ['keywords' => ['judged', 'criticized', 'watched'], 'response' => "That's a hard feeling. You're safe from judgment here."],
    ['keywords' => ['too much', 'heavy', 'overload'], 'response' => "It can be. But you don't have to carry everything alone."],
    ['keywords' => ['ashamed', 'embarrassed', 'disgraced'], 'response' => "You're not alone. Let's talk through what's causing that."],
    ['keywords' => ['no one', 'care', 'ignored'], 'response' => "I care. I really do. And I'm here for you now."],
    ['keywords' => ['fail', 'failure', 'loser'], 'response' => "Failure isn't the end—it's how we learn. You're still growing."],
    ['keywords' => ['pretend', 'fake', 'mask'], 'response' => "It's exhausting wearing a mask. Let it go here. I'm listening."],
    ['keywords' => ['point', 'purpose', 'meaning'], 'response' => "Sometimes we lose sight of meaning. Let's rediscover it together."],
    ['keywords' => ['ignored', 'unseen', 'invisible'], 'response' => "You're seen. I hear you. I value your words."],
    ['keywords' => ['healing', 'recovery', 'change'], 'response' => "By being honest like this. You've already begun."],
    ['keywords' => ['miss', 'old me', 'past'], 'response' => "You're still in there. Let's find your way back."],
    ['keywords' => ['broken', 'damaged', 'ruined'], 'response' => "No, you're just hurting. Healing will come."],
    ['keywords' => ['weak', 'not strong', 'fragile'], 'response' => "You're stronger than you know. You've made it this far."],
    ['keywords' => ['nobody', 'listens', 'alone'], 'response' => "I'm listening now. Say what you need to say."],
    ['keywords' => ['anxious', 'panic', 'worried'], 'response' => "That must be draining. Let's unpack that feeling."],
    ['keywords' => ['burden', 'bother', 'problem'], 'response' => "You're not a burden. You're a person who deserves love."],
    ['keywords' => ['happy again', 'joy', 'peace'], 'response' => "You can get there. Let's take that journey together."]
];
$responses_tl = [
    ['keywords' => ['kumusta', 'hello', 'hi'], 'response' => "Kamusta! Nandito ako para makinig. Ano ang nais mong pag-usapan?"],
    ['keywords' => ['malungkot', 'depres', 'lungkot'], 'response' => "Pasensya na sa iyong nararamdaman. Nandito ako para sa'yo."],
    ['keywords' => ['masaya', 'tuwa', 'galak'], 'response' => "Salamat sa pagbabahagi ng iyong kasiyahan!"],
    ['keywords' => ['tulong', 'sanggunian', 'alalay'], 'response' => "Handa akong tumulong. Ano ang problema mo?"],
    ['keywords' => ['balisa', 'kinakabahan', 'nerbyos'], 'response' => "Normal lamang ang ganitong pakiramdam. Gusto mo bang pag-usapan pa ito?"],
    ['keywords' => ['mag-isa', 'nag-iisa', 'malayo'], 'response' => "Hindi ka nag-iisa. Nandito ako para sa'yo."],
    ['keywords' => ['takot', 'pangamba', 'kabado'], 'response' => "Huminga ka ng malalim. Ligtas ka ngayon. Nandito lang ako."],
    ['keywords' => ['pagod', 'stress', 'ubos'], 'response' => "Karapat-dapat kang magpahinga. Huwag mong kalimutan ang sarili mo."],
    ['keywords' => ['iyak', 'luha', 'umiiyak'], 'response' => "Okay lang umiyak. Malaking bagay na nailalabas mo ang nararamdaman mo."],
    ['keywords' => ['walang silbi', 'wala ako', 'hopeless'], 'response' => "Mahalaga ka. Huwag mong kalimutan ang halaga mo."],
    ['keywords' => ['manhid', 'wala', 'blangko'], 'response' => "Mahirap ang ganyang pakiramdam. Nandito ako, handang makinig."],
    ['keywords' => ['sumuko', 'ayoko na', 'pagod na'], 'response' => "Huwag kang susuko. Nandito ako para sumuporta sa’yo."],
    ['keywords' => ['bakit', 'gulo', 'wala na'], 'response' => "Okay lang na malito. Unti-unti nating aayusin ito."],
    ['keywords' => ['usap', 'kailangan', 'kausap'], 'response' => "Narito ako. Handa akong makinig kahit kailan mo gusto."],
    ['keywords' => ['galit', 'inis', 'sarili'], 'response' => "Patawarin mo ang sarili mo. Karapat-dapat kang mahalin."],
    ['keywords' => ['tulog', 'puyat', 'hindi tulog'], 'response' => "Puwede nating pag-usapan kung anong gumugulo sa isip mo."],
    ['keywords' => ['hindi naiintindihan', 'bale wala', 'iniwan'], 'response' => "Hindi ka nag-iisa. Naiintindihan kita."],
    ['keywords' => ['wasak', 'sirang-sira', 'durog'], 'response' => "Parang sirang-sira ka, pero buo ka pa rin. Nandito ako."],
    ['keywords' => ['ligaw', 'hindi alam', 'wala'], 'response' => "Kahit naliligaw ka ngayon, makakahanap tayo ng daan."],
    ['keywords' => ['ewan', 'di alam', 'malabo'], 'response' => "Ayos lang na hindi mo alam lahat ngayon. Andito ako para samahan ka."],
    ['keywords' => ['pwede ba', 'sekreto', 'kwento'], 'response' => "Oo naman. Anumang gusto mong sabihin, makikinig ako."],
    ['keywords' => ['naipit', 'walang labas', 'nakakulong'], 'response' => "Ang hirap niyan. Gusto mo bang ikuwento kung bakit?"],
    ['keywords' => ['wala', 'manhid', 'tulala'], 'response' => "Kahit parang wala kang nararamdaman, totoo ang nararamdaman mong iyan."],
    ['keywords' => ['may mali', 'sira', 'di tama'], 'response' => "Wala kang mali. Ang damdamin mo ay valid."],
    ['keywords' => ['inaantok', 'walang gana', 'wala sa mood'], 'response' => "Baka pagod ka lang talaga. Ayos lang magpahinga."],
    ['keywords' => ['natatakot', 'kinakabahan', 'gigil'], 'response' => "Naiintindihan ko ang takot mo. Ligtas ka dito."],
    ['keywords' => ['isip', 'sobra', 'di mapakali'], 'response' => "Nakakapagod ang pag-iisip nang sobra. Ilabas mo dito."],
    ['keywords' => ['baliw', 'iba', 'di normal'], 'response' => "Normal lang ang nararamdaman mo. Huwag mong ikahiya."],
    ['keywords' => ['ayos', 'galing', 'ayusin'], 'response' => "Kaya natin itong ayusin. Isa-isang hakbang lang."],
    ['keywords' => ['hindi ayos', 'malungkot', 'walang gana'], 'response' => "Ayos lang sabihin na hindi ka okay. Handa akong makinig."],
    ['keywords' => ['hiya', 'konsensya', 'sisi'], 'response' => "Lahat tayo nagkakamali. Pag-usapan natin ito."],
    ['keywords' => ['stress', 'pag-aalinlangan', 'sobra'], 'response' => "Hinga muna tayo. Isa-isa lang. Hindi ka nag-iisa."],
    ['keywords' => ['kaya ko ba', 'paano na', 'di ko alam'], 'response' => "Nandito ako. Sabay nating haharapin ito."],
    ['keywords' => ['pahinga', 'puyat', 'tigil'], 'response' => "Puwede kang magpahinga. Hindi mo kailangang magpaliwanag."],
    ['keywords' => ['hinusgahan', 'pinuna', 'pinagtatawanan'], 'response' => "Walang huhusga sa'yo rito. Safe space ito."],
    ['keywords' => ['marami', 'sobra sobra', 'di ko kaya'], 'response' => "Ang bigat niyan. Pero hindi mo kailangang buhatin mag-isa."],
    ['keywords' => ['hiya', 'kahihiyan', 'napahiya'], 'response' => "Minsan nakakahiyang aminin, pero valid ang nararamdaman mo."],
    ['keywords' => ['walang pakialam', 'iniwan', 'nag-iisa'], 'response' => "Ako'y narito. May pakialam ako."],
    ['keywords' => ['palpak', 'bagsak', 'talo'], 'response' => "Ang pagkakamali ay bahagi ng pagkatuto. Hindi ka palpak."],
    ['keywords' => ['kunwari', 'maskara', 'panggap'], 'response' => "Nakakapagod magkunwari. Dito, totoo ka."],
    ['keywords' => ['bakit ako', 'bakit ganito', 'wala akong kwenta'], 'response' => "Mahalaga ka. Huwag mong kalimutan iyan."],
    ['keywords' => ['di kita', 'wala ako', 'hindi kita'], 'response' => "Nakikita kita. Mahalaga ka sa akin."],
    ['keywords' => ['pag-ayos', 'pagbangon', 'pagbabago'], 'response' => "Magsimula tayo ngayon. Unti-unti lang."],
    ['keywords' => ['namimiss ko', 'dati ako', 'lumang ako'], 'response' => "Andiyan ka pa rin. Hanapin natin ulit ang sarili mo."],
    ['keywords' => ['sira', 'basag', 'durog'], 'response' => "Hindi ka sira. Nasaktan ka lang."],
    ['keywords' => ['mahina', 'hindi matatag', 'marupok'], 'response' => "Malakas ka. Hindi madali pero lumalaban ka."],
    ['keywords' => ['walang nakikinig', 'di marinig', 'walang paki'], 'response' => "Nakikinig ako. Sabihin mo lang."],
    ['keywords' => ['panic', 'kabado', 'di mapakali'], 'response' => "Malalim na hinga. Nandito ako."],
    ['keywords' => ['istorbo', 'abala', 'pasanin'], 'response' => "Hindi ka abala. Isa kang taong mahalaga."],
    ['keywords' => ['maging masaya', 'kapayapaan', 'pag-asa'], 'response' => "Posible iyan. Sama-sama nating hanapin."],
    ['keywords' => ['owow', 'wow', 'wowow'], 'response' => "Naay nasunog, WOW WOW WOW WOW! ka nindot sang kalayo! hala kahayag"],
];
$responses_hil = [
    ['keywords' => ['kamusta', 'hello', 'hi'], 'response' => "Kamusta! Ari ko di para mamati. Ano gusto mo istoryahan?"],
    ['keywords' => ['maluoy', 'masubo', 'kalain'], 'response' => "Pasensya gid sa pagbatyag mo subong. Ari ko para sa imo."],
    ['keywords' => ['lipay', 'kalipay', 'masadya'], 'response' => "Nalipay ako nga ginshare mo ang imo kalipay nga nabatyagan!"],
    ['keywords' => ['bulig', 'tabang', 'sabat'], 'response' => "Handa gid ko magbulig. Ano ang imo problema?"],
    ['keywords' => ['kabala', 'kulba', 'nerbyos'], 'response' => "Normal lang ang pagbatyag sini. Gusto mo pa istoryahan?"],
    ['keywords' => ['isa', 'wala upod', 'nagapangita'], 'response' => "Ari ko ya. Biskan daw nagaisahanon ka."],
    ['keywords' => ['kahadlok', 'kurog', 'ginakulbaan'], 'response' => "Ginabatyag mo ina bangud importante ang imo ginaatubang. Ari lang ko."],
    ['keywords' => ['kapoy', 'stress', 'ginakapoy'], 'response' => "Sige lang. Pahuway anay. Importante ang imo kabuhi."],
    ['keywords' => ['hibi', 'luha', 'nagahibi'], 'response' => "Okay lang maghibi. Ipagwa mo lang imo nabatyagan."],
    ['keywords' => ['wala pulos', 'hopeless', 'ginakapoy'], 'response' => "Importante ka gid. May pulos ka, bisan indi mo mabatyagan subong."],
    ['keywords' => ['wala gana', 'manhid', 'blangko'], 'response' => "Ginabatyag mo ina kay damo ka ginapanumdum. Diri lang ko."],
    ['keywords' => ['suko', 'untat', 'ayaw na'], 'response' => "Biskan gusto mo na mag-untat, indi ka nagaisahanon. Padayon lang."],
    ['keywords' => ['wala ko kabalo', 'gubot', 'kalibugan'], 'response' => "Okay lang nga gubot imo paminsaron. Puwede ta ini istoryahan."],
    ['keywords' => ['istorya', 'hambal', 'istoryahi'], 'response' => "Handa ko pamati. Istoryahi lang ako bisan ano."],
    ['keywords' => ['akig', 'kalain buot', 'kaakig'], 'response' => "Ang akig normal lang. Istoryahon ta kung ngaa ka amo sina."],
    ['keywords' => ['kulang tulog', 'puyat', 'wala pahulay'], 'response' => "Basi kulang ka lang sa tulog. Pahuway gid anay."],
    ['keywords' => ['wala kasabot', 'lisod', 'wala ginbatyag'], 'response' => "Biskan daw indi mo masaysay imo ginabatyag, okay lang ina."],
    ['keywords' => ['guba', 'buak', 'wala na'], 'response' => "Biskan daw guba na, pwede pa na. Ari ko diri."],
    ['keywords' => ['wala na', 'lagaw', 'diin ko'], 'response' => "Biskan nagalibog ka subong, may padulungan pa. Updan ta ka."],
    ['keywords' => ['ambot', 'malabo', 'di ko kabalo'], 'response' => "Okay lang nga indi mo kabalo tanan. Dungan ta ni sulbaron."],
    ['keywords' => ['pwede', 'sekreto', 'hambalan'], 'response' => "Oo. Puwede ka gid maghambal sa akon. Indi ako maghusga."],
    ['keywords' => ['naipit', 'wala guwa', 'ginaduot'], 'response' => "Ginahambal mo ina bangud daw ginaduot ka. Gusto mo istoryahan?"],
    ['keywords' => ['wala', 'manhid', 'tulala'], 'response' => "Biskan daw wala ka sang ginabatyag, may kabug-at ina."],
    ['keywords' => ['sala', 'guba', 'mali'], 'response' => "Wala ka sang sala. Basi ginakabudlayan ka lang."],
    ['keywords' => ['ginakapoy', 'wala gana', 'bug-at'], 'response' => "Kapoy gid ya usahay. Pahuway lang anay."],
    ['keywords' => ['hadlok', 'naguol', 'kulbaan'], 'response' => "Indi ka magkahadlok maghambal. Ari lang ako."],
    ['keywords' => ['paminsar', 'bug-at', 'huot'], 'response' => "Ang bug-at sang paminsaron lisod gid. Pwede mo ko istoryahan."],
    ['keywords' => ['lain', 'baliw', 'di normal'], 'response' => "Wala problema kung daw lain imo pamatyag. Ginabaton ta na diri."],
    ['keywords' => ['ayos', 'buligan', 'bangon'], 'response' => "Bangon lang kita liwat. Isa ka adlaw sa isa ka tion."],
    ['keywords' => ['di okay', 'masubo', 'bug-at buot'], 'response' => "Okay lang nga indi ka okay. Importante kabalo ka maghambal."],
    ['keywords' => ['sala ko', 'may sala', 'nagsala'], 'response' => "Ang tanan nagaagi sini. Wala ka nagaisahanon."],
    ['keywords' => ['pressure', 'stress', 'kulba'], 'response' => "Isa-isa lang. Indi mo kinahanglan madali."],
    ['keywords' => ['paano', 'di ko kaya', 'kabudlay'], 'response' => "Biskan budlay, makaya mo. Updan ta ka."],
    ['keywords' => ['pahuway', 'relax', 'untat'], 'response' => "Importante ang pahuway. Indi mo kinahanglan magsakripisyo permi."],
    ['keywords' => ['ginhusgahan', 'ginkastigo', 'pinuna'], 'response' => "Wala sang may nagahusga diri. Safe ka di maghambal."],
    ['keywords' => ['sobrang damo', 'bug-at tanan', 'daugdaug'], 'response' => "Damo man, pero indi mo na kinahanglan dal-on isa ka imo lang."],
    ['keywords' => ['kaulaw', 'nakahuya', 'ulihi'], 'response' => "Indi ka dapat maghuya. Natural lang ina nga balatyagon."],
    ['keywords' => ['ginbayaan', 'wala kabalo', 'nagaisahan'], 'response' => "Ari lang ko. Indi taka pagbayaan."],
    ['keywords' => ['palpak', 'sala', 'kahuya'], 'response' => "Okay lang masala. Parte ina sang kabuhi."],
    ['keywords' => ['naga-pretend', 'gapanglimbong', 'nagapilit'], 'response' => "Indi mo kinahanglan magpakuno-kuno diri. Maging totoo ka."],
    ['keywords' => ['ngaa ako', 'wala pulos', 'may sala'], 'response' => "May ara ka pulos. Importante nga istoryahan ta ini."],
    ['keywords' => ['di makita', 'invisible', 'wala kabalo'], 'response' => "Nakita ta ka. Kilala ta ka. Importante ka."],
    ['keywords' => ['pagbag-o', 'bangon', 'liwaton'], 'response' => "Makaya mo magbangon. Untat lang anay tapos try liwat."],
    ['keywords' => ['namimiss ko', 'dati ko', 'lumang ako'], 'response' => "Andam ko mamati kon gusto mo mahibalik imo kaugalingon."],
    ['keywords' => ['buak', 'basag', 'sirado'], 'response' => "Biskan daw buak na ka, pwede ka pa gid mahimu nga mas maayo."],
    ['keywords' => ['maluya', 'huyang', 'wala kusog'], 'response' => "Luyag ko ipabalo nga biskan maluya ka subong, magpakabaskog ka gihapon."],
    ['keywords' => ['wala gapamati', 'wala pakialam', 'tani may gasabat'], 'response' => "Ari ko ya. Gapamati ako. Hambala lang ko."],
    ['keywords' => ['panic', 'kulba', 'pas-pas'], 'response' => "Ginakulbaan ka? Ginhawa anay. Ari ko di."],
    ['keywords' => ['istorbo', 'perwisyo', 'ginapabay-an'], 'response' => "Indi ka istorbo. Importante ka."],
    ['keywords' => ['malipay', 'kalinong', 'paglaum'], 'response' => "May paglaum pa gid. Updan ta ka sa pagpangita."],
];

 switch($lang) {
     case 'tl': $responses = $responses_tl; $response = "Pa umanhin, hindi kita na intindihan, pwede mo bang ulitin?"; break;
     case 'hil': $responses = $responses_hil; $response = "Pansenya, indi taka ma inchindihan, pwede mo suliton?" ;break;
     default: $responses = $responses_en; $response = "Sorry, I don't understand that. Could you please rephrase?" ;break;
 }

 foreach ($responses as $item) {
     foreach ($item['keywords'] as $keyword) {
if (strpos($userQuestion, $keyword) !== false) {
$response = $item['response'];
break;
}
     }
 }

 echo json_encode(['response' => $response]);
 exit;
} elseif ($ajax_type === '2') {
 $conversation_json = $_POST['conversation'] ?? '';
 if (!$conversation_json) {
     echo json_encode(['success' => false, 'error' => 'No conversation data received']);
     exit;
 }

 $conv_array = json_decode($conversation_json, true);
 if (!is_array($conv_array)) {
     echo json_encode(['success' => false, 'error' => 'Invalid conversation JSON']);
     exit;
 }

 $first_question = '';
 foreach ($conv_array as $line) {
     if ($line['sender'] === 'user') {
$first_question = $line['text'];
break;
     }
 }

 $timestamp = date('mdhis');
 $filename = "conversation_{$timestamp}_{$first_question}.txt";
 $filepath = $convo_dir . DIRECTORY_SEPARATOR . $filename;

 $content_lines = [];
 foreach($conv_array as $line) {
     $sender = ($line['sender'] === 'user') ? 'You' : 'Bot';
     $text = str_replace(["\r","\n"], ' ', $line['text']);
     $content_lines[] = $sender . ': ' . $text;
 }
 $content_str = implode("\n", $content_lines);

 if (file_put_contents($filepath, $content_str) !== false) {
     echo json_encode(['success' => true, 'filename' => $filename]);
 } else {
     echo json_encode(['success' => false, 'error' => 'Failed to write file']);
 }
 exit;

} elseif ($ajax_type === '3') {
 $files = scandir($convo_dir);
 $convos = [];
 foreach ($files as $file) {
     if ($file === '.' || $file === '..') continue;
     if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
$convos[] = $file;
     }
 }
 rsort($convos, SORT_STRING);
 echo json_encode(['success' => true, 'files' => $convos]);
 exit;
} elseif ($ajax_type === '4') {
 $file = $_POST['filename'] ?? '';

 $filepath = $convo_dir . DIRECTORY_SEPARATOR . $file;
 if (!file_exists($filepath)) {
     echo json_encode(['success' => false, 'error' => 'File not found']);
     exit;
 }
 $content = file_get_contents($filepath);
 $lines = explode("\n", $content);
 $conversation = [];
 foreach($lines as $line) {
     $line = trim($line);
     if (!$line) continue;
     if (strpos($line, 'You: ') === 0) {
$conversation[] = ['sender' => 'user', 'text' => substr($line, 5)];
     } elseif (strpos($line, 'Bot: ') === 0) {
$conversation[] = ['sender' => 'bot', 'text' => substr($line, 5)];
     }
 }
 echo json_encode(['success' => true, 'conversation' => $conversation]);
 exit;
}
echo json_encode(['success' => false, 'error' => 'Unknown ajax type']);
exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mental Health Listener</title>
    <link rel="icon" href="graphics/logo.png" style="filter: invert(100%);">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="history-panel" id="historyPanel" aria-label="Conversation history">
<div class="history-header">Chat History</div>
<div class="history-items" id="historyItems"></div>
</div>

<div class="chat-container" role="main">
<div class="chat-main" aria-live="polite" aria-atomic="false">
 <nav aria-label="Chat navigation">
     <h2 id="chatTitle"><img src="graphics/logo.png" class="logo">Mental Health Listener</h2>
     <select id="langSelect" aria-label="Select language">
<option value="en" selected>English</option>
<option value="tl">Tagalog</option>
<option value="hil">Hiligaynon</option>
     </select>
     <button id="saveButton" aria-label="Save conversation and start new chat" title="Save & New Chat">+ New Chat</button>
 </nav>

 <div class="messages" id="chat" role="log" aria-live="polite" aria-relevant="additions"></div>

 <form id="chatForm" aria-label="Send new message">
     <input type="text" id="questionInput" name="question" placeholder="Type your question..." aria-required="true" autocomplete="off" />
     <button type="submit">Send</button>
 </form>
</div>
</div>
<script src="script.js"></script>
</body>
</html>