/*
  Translit JS class.
  v.1.0
  24 October 2004

  ---------

  �������������� ������ (���������� �� � ������������ � �������� URL).
  ��������� ����� � ����� ��������, � ������� + ����� ���������� �������������
  ����� �� �������� (������� ����� ������ ��� ����� ������)

  ���������: http://pixel-apes.com/translit

  ---------

  * UrlTranslit( str, allow_slashes ) 
    -- ������������� ������ � "�������� �������� URL"

  * Supertag( str, allow_slashes )    
    -- ������������� ������ � "��������" -- �������� ������� 
       �������������, ��������� �� ��������� ���� � ����.

  * BiDiTranslit( str, direction_decode, allow_slashes ) 
    -- ������������� ������ � "��������� ���������� URL"
       � ������������ ��������������.
       ��������� ������ �������� � �������� "true", �� ������ 
       ������������ ������ ������� � ��������������� ��������.

  * �� ���� �������� ���� �������������� �������� "allow_slashes", �������
    ��������� ���, ������������ �� ������ "/", ��������� ��� ��������������, 
    ���� ������� ��� �� ������.

=============================================================== (Mendokusee@pixeapes)
*/

function Translit()
{
  this.enabled = true;
}

// ------------------------------------------------------------------------
// ------------------------------------------------------------------------

Translit.prototype.UrlTranslit = function( str, allow_slashes )
{
   var slash = "";
   if (allow_slashes) slash = "\\/";
   
   var LettersFrom = "������������������������";
   var LettersTo   = "abvgdeziklmnoprstufyejxe";
   var Consonant = "���������������������";
   var Vowel = "���������";
   var BiLetters = {  
     "�" : "zh", "�" : "ts",  "�" : "ch", 
     "�" : "sh", "�" : "sch", "�" : "ju", "�" : "ja"
                   };

   str = str.replace( /[_\s\.,?!\[\](){}]+/g, "_");
   str = str.replace( /-{2,}/g, "--");
   str = str.replace( /_\-+_/g, "--");

   str = str.toLowerCase();


   //here we replace �/� 
   str = str.replace( 
      new RegExp( "(�|�)(["+Vowel+"])", "g" ), "j$2");
   str = str.replace( /(�|�)/g, "");

   //transliterating
   var _str = "";
   for( var x=0; x<str.length; x++)
    if ((index = LettersFrom.indexOf(str.charAt(x))) > -1)
     _str+=LettersTo.charAt(index);
    else
     _str+=str.charAt(x);
   str = _str;

   var _str = "";
   for( var x=0; x<str.length; x++)
    if (BiLetters[str.charAt(x)])
     _str+=BiLetters[str.charAt(x)];
    else
     _str+=str.charAt(x);
   str = _str;

   str = str.replace( /j{2,}/g, "j");

   str = str.replace( new RegExp( "[^"+slash+"0-9a-z_\\-]+", "g"), "");

   return str;
}

Translit.prototype.Supertag = function( str, allow_slashes )
{
   var slash = "";
   if (allow_slashes) slash = "\\/";

   str = this.UrlTranslit( str, allow_slashes );

   str = str.replace( new RegExp( "[^"+slash+"0-9a-zA-Z\\-]+", "g"), "");
   str = str.replace( /[\-_]+/g, "-");
   str = str.replace( /-+$/g, "");

   return str;
}


Translit.prototype.BiDiTranslit = function( str, direction_decode, allow_slashes )
{
   var Tran = {
    "�" : "A",  "�" : "B",  "�" : "V",  "�" : "G",  "�" : "D",  "�" : "E",  "�" : "JO",  "�" : "ZH",  "�" : "Z",  "�" : "I",
    "�" : "JJ", "�" : "K",  "�" : "L",  "�" : "M",  "�" : "N",  "�" : "O",  "�" : "P",   "�" : "R",   "�" : "S",  "�" : "T",
    "�" : "U",  "�" : "F",  "�" : "KH",  "�" : "C",  "�" : "CH", "�" : "SH", "�" : "SHH", "�" : "_~",   "�" : "Y",  "�" : "_'",
    "�" : "EH", "�" : "JU", "�" : "JA", "�" : "a",  "�" : "b",  "�" : "v",  "�" : "g",   "�" : "d",   "�" : "e",  "�" : "jo",
    "�" : "zh", "�" : "z",  "�" : "i",  "�" : "jj", "�" : "k",  "�" : "l",  "�" : "m",   "�" : "n",   "�" : "o",  "�" : "p",
    "�" : "r",  "�" : "s",  "�" : "t",  "�" : "u",  "�" : "f",  "�" : "kh",  "�" : "c",   "�" : "ch",  "�" : "sh", "�" : "shh",
    "�" : "~",  "�" : "y",  "�" : "'",  "�" : "eh", "�" : "ju", "�" : "ja", " " : "__", "_" : "__"
              };
   // note how DeTran is sorted. That is one of MAJOR differences btwn PHP & JS versions
   var DeTran = {
    "SHH"  : "�", // note this is tri-letter
    "CH"   : "�",  "SH"   : "�", "EH"   : "�",  "JU"    : "�",  "_'"   : "�",  "_~"   : "�", 
    "JO"   : "�",  "ZH"   : "�", "JJ"   : "�",  "KH"    : "�",  "JA"   : "�",  // note they are bi-letters
    "A"    : "�",  "B"    : "�",  "V"   : "�",  "G"     : "�",  "D"    : "�",  "E"    : "�",  
    "Z"    : "�",  "I"    : "�",  "K"   : "�",  "L"     : "�",  "M"    : "�",  "N"    : "�",  
    "O"    : "�",  "P"    : "�",  "R"   : "�",  "S"     : "�",  "T"    : "�",  "U"    : "�",  
    "F"    : "�",  "C"    : "�",  "Y"   : "�",  
    "shh"  : "�", // small tri-letters
    "jo"   : "�",  "zh"   : "�",   "jj"   : "�",  "kh"   : "�",  "ch"   : "�",  "sh"   : "�",
    "ju"   : "�",  "ja"   : "�",   "__" : " ", // small bi-letters
    "a"    : "�",  "b"     : "�",  "v"    : "�",  "g"    : "�",  "d"    : "�",  "e"    : "�",  
    "z"    : "�",  "i"     : "�",  "k"    : "�",  "l"    : "�",  "m"    : "�",  "n"    : "�",
    "o"    : "�",  "p"     : "�",  "r"    : "�",  "s"    : "�",  "t"    : "�",  "u"    : "�",  
    "f"    : "�",  "c"     : "�",  "~"    : "�",  "y"    : "�",  "'"    : "�",  "eh"   : "�"
              };

   var result = "";
   if (!direction_decode)
   {
     str = str.replace( /[^\/\- _0-9a-z�-��-߸�]/gi, "" );
     if (!allow_slashes) str = str.replace( /[^\/]/i, "");

     // ������ -- "�������" ������
     // ��� ��������� ��-����� -- "����������" �������
     var is_rus = new RegExp( "[�-��-߸� ]", "i" );

     // �������� �� ������, �������� � "������-��������"
     var lang_eng = true;
     var _lang_eng = true;
     var temp;
     for (var i=0; i<str.length; i++)
     {
       _lang_eng = lang_eng;
       temp = String(str.charAt(i));
       if (temp.replace(is_rus, "") == temp) 
         lang_eng = true;
       else // convert; this conversion is the second MAJOR difference.
       {
         lang_eng = false;
         temp = Tran[ temp ]; 
       }
       if (lang_eng != _lang_eng) temp = "+"+temp;
       result += temp;
     }
   }
   else
   {
     var pgs = str.split("/");
     var DeTranRegex = new Array();
     for (var k in DeTran)
       DeTranRegex[k] = new RegExp( k, "g" );
     for (var j=0; j<pgs.length; j++)
     {
       var strings = pgs[j].split("+");
       for (var i=1; i<strings.length; i+=2)
         for (var k in DeTran)
           strings[i] = strings[i].replace( DeTranRegex[k], DeTran[k] );
       pgs[j] = strings.join("");
     }
     result = pgs.join( allow_slashes?"/":":" );
   }

   return result.replace( /\/+$/, "" );
}


