<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TextAnalysis\Documents\TokensDocument;
use TextAnalysis\Collections\DocumentArrayCollection;
use TextAnalysis\Indexes\TfIdf;
use App\Traning;
use App\KmeansData;

class SentimenAnalysisController extends Controller
{
    public $stemmerFactory;
    public $stemmer;
    public $stopWordRemoverFactory;
    public $stopWordRemove;
    public $tokenizerFactory;
    public $tokenizer;
    public function __construct()
    {
        //Membuat constuctor library
        ini_set('max_execution_time', 0);
        $this->stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
        $this->stemmer  = $this->stemmerFactory->createStemmer();
        $this->stopWordRemoverFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
        $this->stopWordRemover = $this->stopWordRemoverFactory->createStopWordRemover();
        $this->tokenizerFactory  = new \Sastrawi\Tokenizer\TokenizerFactory();
        $this->tokenizer = $this->tokenizerFactory->createDefaultTokenizer();
    }
    public function index()
    {
        //Halaman awal menghitung data training, data training positf, dan data training negatif
        $data['train'] = Traning::All()->count(); //semua data training dihitung
        $data['positif'] = KmeansData::where('class','positif')->count(); // data positif dihitung
        $data['negatif'] = KmeansData::where('class','negatif')->count();// data negatif dihitung

        return view('content.home',compact('data')); // mengirimkan data pada view
    }

    public function Traning()
    {
        //halaman training, menampilkan data training
        $train = KmeansData::all(); //mengambil semua data training
        return view('content.traning',compact('train'));
    }
    public function IndexKmeans()
    {
        $train = Traning::all(); //mengambil semua data training
        $index=0;
        $token = array();
        foreach ($train as $t)
        {
            $t->data = $this->stopWordRemover->remove($t->data); //pre-processing stopword
            $t->data = $this->stemmer->stem($t->data); //preprocessing stemming
            $token['token'][$index] = new TokensDocument($this->tokenizer->tokenize($t->data)); //preporcessing tokenize (setiap dokumen)
            $index++;
        }

        $token['dokumen'] = new DocumentArrayCollection($token['token']); //menggabungkan token setiap dokument
        $token['tfidf']= new TfIdf($token['dokumen']); // Menghitung TFIDF dari hasil tokenize

        $hasil = $this->K_means($token['tfidf']->tfidf,$token['tfidf']->tfidf[30],$token['tfidf']->tfidf[135],$token['tfidf']->term); //menghitung k-means

        $indexDocument = $this->getIndexDoc($hasil[0],$hasil[1]); //mengindex dokumen hasil k-means sesuai data di database

        //mengambil data dari database berdasarkan hasil kmeans dan mengupdate sesuai hasil pengelompokan
        $positif = KmeansData::whereIn("id",$indexDocument[0]);
        $negatif = KmeansData::whereIn("id",$indexDocument[1]);

        $c1['class'] = "positif";
        $c2['class'] = "negatif";
        $positif->update($c1);
        $negatif->update($c2);
        //akhir dari update data label didatabase

        $data = KmeansData::All(); //mengambil data K-Means dari database

        return view('content.k_means',compact('data'));
    }

    //fungsi menambah data training
    public function TambahDataKmeans(Request $r)
    {

        $train = new Traning();
        $train->data = $r->data;
        $train->save();
        $kmean = new KmeansData();
        $kmean->id = $train->id;
        $kmean->data = $r->data;
        $kmean->class = NULL;
        $kmean->save();
        return redirect('/k-means');
    }

    public function Knn($class=null,$test=null)
    {
        $data = KmeansData::All();

        return view('content.knn',compact('data','class','test'));
    }
    public function Evaluasi()
    {
        $k=3; // pengambilan K pada KNN
        $conf_matrix = array();
        $data = KmeansData::All();
        $index=0;
        $token = array();
        foreach ($data as $t)
        {
            $t->data = $this->stopWordRemover->remove($t->data);
            $t->data = $this->stemmer->stem($t->data);
            $token['token'][$index] = new TokensDocument($this->tokenizer->tokenize($t->data));
            $token['class'][$index] = $t->class;
            $index++;
        }
//        return $index;
        $token['dokumen'] = new DocumentArrayCollection($token['token']);
        $token['tfidf']= new TfIdf($token['dokumen']);
        for ($i=0;$i<count($data);$i++){
            $dist = $this->Cosine($token['tfidf']->tfidf[$i],$token['tfidf']->tfidf,$token['tfidf']->term,$token['class']);

            $class1=0;
            $class2=0;
            for ($index=0; $index<$k;$index++){
                if($dist[$index]['class']=="negatif"){
                    $class2 +=1;
                }else{
                    $class1 += 1;
                }
            }
            $clas = ($class1>$class2)?"positif" : "negatif";

            $prediksi[$i] = $clas;
        }
        $confusion = $this->ConfusionMatrix($data,$prediksi);
        return view('content.evaluasi',compact('data','prediksi','confusion'));
    }

    public function distance($centroid,$data,$term){
        $sum = 0;
        for($i=0;$i<count($term);$i++){
            $sum += abs($data[$term[$i]]-$centroid[$term[$i]]);
        }
        return $sum;
    }

    //fungsi mencari centroid baru
    public function newCentroid($tfidf,$term,$class1, $class2){
        $centroid1=array();
        $centroid2=array();

        //c1
        for ($it = 0; $it<count($term) ; $it++) {
            $centroid1[$term[$it]] = 0;
            for($i=0;$i<count($class1);$i++){
                $centroid1[$term[$it]] += $tfidf[$class1[$i]][$term[$it]];
            }
            $dv = (count($class1)==0)?1:count($class1);
            $centroid1[$term[$it]] /= $dv;
        }

        //c2
        for ($it = 0; $it<count($term) ; $it++) {
            $centroid2[$term[$it]] = 0;
            for($i=0;$i<count($class2);$i++){
                $centroid2[$term[$it]] += $tfidf[$class2[$i]][$term[$it]];
            }
            $dv = (count($class2)==0)?1:count($class2);
            $centroid2[$term[$it]] /= $dv;
        }

        $centroid =array();

        array_push($centroid,$centroid1);
        array_push($centroid,$centroid2);
        return $centroid;

    }

    public function K_means($tfidf,$centroid1,$centroid2,$term)
    {
        /*
         * tfidf [id dokumen][term] = score tfidf
         *
         */

        $status=false;
        $bclas1 =array();
        $bclas2 =array();
        $iterasi = 1;
        do{
            $clas1 =array();
            $clas2 =array();
            for ($index =0; $index < count($tfidf); $index++){
                //menghitung distance data training dengan centroid
                $distanceC1 = $this->distance($centroid1,$tfidf[$index],$term);
                $distanceC2 = $this->distance($centroid2,$tfidf[$index],$term);

                //membandingkan lebih dekat ke Centroid yang mana
                if($distanceC1 < $distanceC2){
                    array_push($clas1,$index);
                }else{
                    array_push($clas2,$index);
                }
            }

            //mencari centroid baru
            $CentroNew = $this->newCentroid($tfidf,$term,$clas1,$clas2);
            $centroid1 = $CentroNew[0];
            $centroid2 = $CentroNew[1];
            if($bclas1 == $clas1 && $bclas2 == $clas2){
                break;
            }else{
                $bclas1 = $clas1;
                $bclas2 = $clas2;
            }
            $iterasi++;
        }while(1);

        return array($clas1,$clas2,$centroid1,$centroid2,$iterasi);
    }

    //fungsi mengambil index document di database dari hasil k-means
    public function getIndexDoc($clas1, $clas2)
    {
        $docClass1 = array();
        for ($index =0; $index < count($clas1); $index++){
            array_push($docClass1,$clas1[$index]+1);
        }
        $docClass2 = array();
        for ($index =0; $index < count($clas2); $index++){
            array_push($docClass2,$clas2[$index]+1);
        }

        return array($docClass1,$docClass2);
    }

    //fungsi proses KNN
    public function KnnProccess(Request $r)
    {
        $k=3; // k diset 3
        $data = KmeansData::All(); //mengambil data k-means dari database
        $index=0;
        $token = array();
        foreach ($data as $t)
        {
            $t->data = $this->stopWordRemover->remove($t->data);
            $t->data = $this->stemmer->stem($t->data);
            $token['token'][$index] = new TokensDocument($this->tokenizer->tokenize($t->data));
            $token['class'][$index] = $t->class;
            $index++;
        }

        //preprocessing document testing
        $test = $this->stopWordRemover->remove($r->data);
        $test = $this->stemmer->stem($test);
        $token['token'][$index] = new TokensDocument($this->tokenizer->tokenize($test));

        //menggabungkan token dari training dan testing kedalam satu element
        $token['dokumen'] = new DocumentArrayCollection($token['token']);
        $token['tfidf']= new TfIdf($token['dokumen']); //menghitung tfidf

        //menghitung distance dari KNN dengan Cosing
        /*
         * $token['tfidf']->tfidf : semua tfidf dari dokumen yang ada
         * $token['tfidf']->tfidf[0] : mengambil hasil tfidf dari dokument pertama
         * $token['tfidf']->tfidf[$index] : tfidf dari dokument testing karena dokumen testing ada di index terakhir
         * cosine(dokumen testing, dokumen training, semua term, semua class dari k-means)
         * hasil dari cosine distance sudah disorting
         */
        $dist = $this->Cosine($token['tfidf']->tfidf[$index],$token['tfidf']->tfidf,$token['tfidf']->term,$token['class']);


        //mengambil kesimpulan dari KNN berdasarkan nilai K, K=3 diambil kesimpulan yang label paling dominan
        $class1=0;
        $class2=0;
        for ($index=0; $index<$k;$index++){
            if($dist[$index]['class']=="negatif"){
                $class2 +=1;
            }else{
                $class1 += 1;
            }
        }
        $clas = ($class1>$class2)?"positif" : "negatif";

        //menyimpan data testing dari KNN kedatabase (table training dan k-menas)
        $train = new Traning();
        $train->data = $r->data;
        $train->save();
        $kmean = new KmeansData();
        $kmean->id = $train->id;
        $kmean->data = $r->data;
        $kmean->class = $clas;
        $kmean->save();

        return $this->Knn($clas,$r->data);
    }

    public function Cosine($testing, $training,$term,$class)
    {
        $skalar =array();
        $panjangVektor =array();
        $dist = array();
        for ($index=0;$index<count($training)-1;$index++){
            $skalar[$index] = 0;
            $panjangVektor['testing'] = 0;
            $panjangVektor[$index] = 0;
            $dist[$index] = array();
            for ($it = 0;$it<count($term);$it++){
                $skalar[$index]+= ($testing[$term[$it]]*$training[$index][$term[$it]]); //menhitung perkalian skalar pada dokumen training dan testing
                $panjangVektor['testing']+= ($testing[$term[$it]]*$testing[$term[$it]]); //menghitung panjang vector dokumen testing
                $panjangVektor[$index]+= ($training[$index][$term[$it]]*$training[$index][$term[$it]]);//menghitung panjang vector dokument training
            }

            //akar hasil panjang vector
            $panjangVektor['testing'] = sqrt($panjangVektor['testing']);
            $panjangVektor[$index] = sqrt($panjangVektor[$index]);

            //perhitungan distance
            $cos_distance = $skalar[$index] / ($panjangVektor['testing']*$panjangVektor[$index]);
            $dist[$index]['distance'] = $cos_distance;
            $doc = $index +1;
            $dist[$index]['document'] = $doc; //memasukan index dokumen kedalam hasil distance
            $dist[$index]['class'] = $class[$index]; //memasukan label kedalam data hasil distance
        }

        //sorting
        for ($i=0;$i<count($dist);$i++){
            for ($j=$i+1;$j<count($dist);$j++){
                if ($dist[$j]['distance'] > $dist[$i]['distance']){
                    $temp = $dist[$i];
                    $dist[$i] = $dist[$j];
                    $dist[$j] = $temp;
                }
            }
        }


        return $dist;
    }

    public function ConfusionMatrix($actual, $prediksi)
    {
        $conf['TP'] = 0;
        $conf['TN'] = 0;
        $conf['FP'] = 0;
        $conf['FN'] = 0;
        $index=0;
        foreach ($actual as $ac)
        {
            if($prediksi[$index] =="positif" && $ac->class == "positif"){
                $conf['TP'] += 1;
            }else if($prediksi[$index] == "negatif" && $ac->class == "negatif"){
                $conf['TN'] += 1;
            }else if($ac->class =="positif" && $prediksi[$index] == "negatif"){
                $conf['FP'] += 1;
            }else if($ac->class == "negatif" && $prediksi[$index] == "positif"){
                $conf['FN'] += 1;
            }
            $index++;
        }

        return $conf;
    }
}
