 // Вспомогательные переменные и функции. Встречаются и в других скриптах библиотеки
 // Supplementary vars & funcs. Could be found redeclared in other scripts, therefore it is better to meld`em together
 var isDOM=document.getElementById;
 var isIE=document.all && document.all.item;
 var isMZ=isDOM && (navigator.appName=="Netscape");
 var isO=window.opera && isDOM;

 // SGN(X) = -1, 0, 1
 function sign(x)
 { if (x > 0) return 1; if (x < 0) return -1; return 0; }


 // плавная моталка по странице. Это супер-фича.
 function travelA( Aname, quick, noplus )
 {
   if (isMZ) 
   { 
     var year = navigator.userAgent.substr(navigator.userAgent.indexOf("Gecko/")+6,4);
     if (year=="2002" || year=="2001") isMZ=false;
   } 
   var value=100;
   if (noplus) value=0;
   if (document.all)
     z = document.all[Aname];
   else
   {
     a = document.getElementsByTagName("A");
     aLength = a.length;
     for (var i = 0; i < aLength; i++)
     {
       an = a[i].getAttribute("name");  
       if (an!=null && an==Aname) break;
     }
     z = a[i];
   }
   var x=0;
   var y=0;
   do 
   {
     x += parseInt(isNaN(parseInt(z.offsetLeft))?0:z.offsetLeft);
     y += parseInt(isNaN(parseInt(z.offsetTop))?0:z.offsetTop);
   } 
   while (z=z.offsetParent);
   travelTo( x,  y-value, quick );
   return true;
 }

 // часть прикольной, завораживающей моталки
 function travelTo(x, y, quick )
 {
   var d = document.body;
   if ((document.compatMode) && (document.compatMode == "CSS1Compat")) d = document.documentElement;
   do
     {
       ox = d.scrollLeft;
       oy = d.scrollTop;
       dx = (x - ox) / (quick?1:10);
       dx = sign(dx) * Math.ceil(Math.abs(dx));
       dy = (y - oy) / (quick?1:10);
       dy = sign(dy) * Math.ceil(Math.abs(dy));
       window.scrollBy(dx, dy);
       cx = d.scrollLeft;
       cy = d.scrollTop;
     }
   while (!quick && 
          (( (ox-cx) != 0 ) || ( (oy-cy) != 0 ))
         );
 }

 // автоприклеивание ко всем #-ссылкам чудо-функций
 function travelInit() 
 {
   var a = document.all ? document.all : document.getElementsByTagName("*");
   aLength = a.length;
   var l = window.location.href;
   if (l.indexOf("#")!=-1) l = l.substr(0,l.indexOf("#"));

   for (var i = 0; i < aLength; i++)
   {
     if ((a[i].tagName == "A") || (a[i].tagName == "a"))
     {
       var ahref = a[i].getAttribute("href");
       
       if ( (ahref != null) && 
            ( ( (ahref.substr(0, l.length)==l) && (ahref.charAt(l.length)=="#")
              ) 
              || ahref.charAt(0)=="#"
            )
          )
       {
         if (ahref.charAt(0)=="#") 
           ah = ahref.substr(1, ahref.length-1);
         else 
           ah = ahref.substr(l.length+1, ahref.length-l.length-1);

         a[i].setAttribute("travel", ah);
         a[i].onclick = function (e) { return travelAuto(e); };
       }
     }
   }
 }

 // та функция, которая и приклеивается в travelInit
 function travelAuto(e)
 {
   d = window.event ? window.event.srcElement : e.currentTarget;
   if (!d.getAttribute("travel")) return;
   s = d.getAttribute("travel");
   return travelA(s);
 }

