
﻿
function _phpjs_shared_bc(){var libbcmath={PLUS:'+',MINUS:'-',BASE:10,scale:0,bc_num:function(){this.n_sign=null;this.n_len=null;this.n_scale=null;this.n_value=null;this.toString=function(){var r,tmp;tmp=this.n_value.join('');r=((this.n_sign==libbcmath.PLUS)?'':this.n_sign)+tmp.substr(0,this.n_len);if(this.n_scale>0){r+='.'+tmp.substr(this.n_len,this.n_scale);}
return r;};},bc_add:function(n1,n2,scale_min){var sum,cmp_res,res_scale;if(n1.n_sign===n2.n_sign){sum=libbcmath._bc_do_add(n1,n2,scale_min);sum.n_sign=n1.n_sign;}else{cmp_res=libbcmath._bc_do_compare(n1,n2,false,false);switch(cmp_res){case-1:sum=libbcmath._bc_do_sub(n2,n1,scale_min);sum.n_sign=n2.n_sign;break;case 0:res_scale=libbcmath.MAX(scale_min,libbcmath.MAX(n1.n_scale,n2.n_scale));sum=libbcmath.bc_new_num(1,res_scale);libbcmath.memset(sum.n_value,0,0,res_scale+1);break;case 1:sum=libbcmath._bc_do_sub(n1,n2,scale_min);sum.n_sign=n1.n_sign;}}
return sum;},bc_compare:function(n1,n2){return libbcmath._bc_do_compare(n1,n2,true,false);},_one_mult:function(num,n_ptr,size,digit,result,r_ptr){var carry,value;var nptr,rptr;if(digit===0){libbcmath.memset(result,0,0,size);}else{if(digit==1){libbcmath.memcpy(result,r_ptr,num,n_ptr,size);}else{nptr=n_ptr+size-1;rptr=r_ptr+size-1;carry=0;while(size-->0){value=num[nptr--]*digit+carry;result[rptr--]=value%libbcmath.BASE;carry=Math.floor(value/libbcmath.BASE);}
if(carry!==0){result[rptr]=carry;}}}},bc_divide:function(n1,n2,scale){var quot;var qval;var num1,num2;var ptr1,ptr2,n2ptr,qptr;var scale1,val;var len1,len2,scale2,qdigits,extra,count;var qdig,qguess,borrow,carry;var mval;var zero;var norm;var ptrs;if(libbcmath.bc_is_zero(n2)){return-1;}
if(libbcmath.bc_is_zero(n1)){return libbcmath.bc_new_num(1,scale);}
if(n2.n_scale===0){if(n2.n_len===1&&n2.n_value[0]===1){qval=libbcmath.bc_new_num(n1.n_len,scale);qval.n_sign=(n1.n_sign==n2.n_sign?libbcmath.PLUS:libbcmath.MINUS);libbcmath.memset(qval.n_value,n1.n_len,0,scale);libbcmath.memcpy(qval.n_value,0,n1.n_value,0,n1.n_len+libbcmath.MIN(n1.n_scale,scale));}}
scale2=n2.n_scale;n2ptr=n2.n_len+scale2-1;while((scale2>0)&&(n2.n_value[n2ptr--]===0)){scale2--;}
len1=n1.n_len+scale2;scale1=n1.n_scale-scale2;if(scale1<scale){extra=scale-scale1;}else{extra=0;}
num1=libbcmath.safe_emalloc(1,n1.n_len+n1.n_scale,extra+2);if(num1===null){libbcmath.bc_out_of_memory();}
libbcmath.memset(num1,0,0,n1.n_len+n1.n_scale+extra+2);libbcmath.memcpy(num1,1,n1.n_value,0,n1.n_len+n1.n_scale);len2=n2.n_len+scale2;num2=libbcmath.safe_emalloc(1,len2,1);if(num2===null){libbcmath.bc_out_of_memory();}
libbcmath.memcpy(num2,0,n2.n_value,0,len2);num2[len2]=0;n2ptr=0;while(num2[n2ptr]===0){n2ptr++;len2--;}
if(len2>len1+scale){qdigits=scale+1;zero=true;}else{zero=false;if(len2>len1){qdigits=scale+1;}else{qdigits=len1-len2+scale+1;}}
qval=libbcmath.bc_new_num(qdigits-scale,scale);libbcmath.memset(qval.n_value,0,0,qdigits);mval=libbcmath.safe_emalloc(1,len2,1);if(mval===null){libbcmath.bc_out_of_memory();}
if(!zero){norm=Math.floor(10/(n2.n_value[n2ptr]+1));if(norm!=1){libbcmath._one_mult(num1,0,len1+scale1+extra+1,norm,num1,0);libbcmath._one_mult(n2.n_value,n2ptr,len2,norm,n2.n_value,n2ptr);}
qdig=0;if(len2>len1){qptr=len2-len1;}else{qptr=0;}
while(qdig<=len1+scale-len2){if(n2.n_value[n2ptr]==num1[qdig]){qguess=9;}else{qguess=Math.floor((num1[qdig]*10+num1[qdig+1])/n2.n_value[n2ptr]);}
if(n2.n_value[n2ptr+1]*qguess>(num1[qdig]*10+num1[qdig+1]-n2.n_value[n2ptr]*qguess)*10+num1[qdig+2]){qguess--;if(n2.n_value[n2ptr+1]*qguess>(num1[qdig]*10+num1[qdig+1]-n2.n_value[n2ptr]*qguess)*10+num1[qdig+2]){qguess--;}}
borrow=0;if(qguess!==0){mval[0]=0;libbcmath._one_mult(n2.n_value,n2ptr,len2,qguess,mval,1);ptr1=qdig+len2;ptr2=len2;for(count=0;count<len2+1;count++){if(ptr2<0){val=num1[ptr1]-0-borrow;}else{val=num1[ptr1]-mval[ptr2--]-borrow;}
if(val<0){val+=10;borrow=1;}else{borrow=0;}
num1[ptr1--]=val;}}
if(borrow==1){qguess--;ptr1=qdig+len2;ptr2=len2-1;carry=0;for(count=0;count<len2;count++){if(ptr2<0){val=num1[ptr1]+0+carry;}else{val=num1[ptr1]+n2.n_value[ptr2--]+carry;}
if(val>9){val-=10;carry=1;}else{carry=0;}
num1[ptr1--]=val;}
if(carry==1){num1[ptr1]=(num1[ptr1]+1)%10;}}
qval.n_value[qptr++]=qguess;qdig++;}}
qval.n_sign=(n1.n_sign==n2.n_sign?libbcmath.PLUS:libbcmath.MINUS);if(libbcmath.bc_is_zero(qval)){qval.n_sign=libbcmath.PLUS;}
libbcmath._bc_rm_leading_zeros(qval);return qval;},MUL_BASE_DIGITS:80,MUL_SMALL_DIGITS:(this.MUL_BASE_DIGITS/4),bc_multiply:function(n1,n2,scale){var pval;var len1,len2;var full_scale,prod_scale;len1=n1.n_len+n1.n_scale;len2=n2.n_len+n2.n_scale;full_scale=n1.n_scale+n2.n_scale;prod_scale=libbcmath.MIN(full_scale,libbcmath.MAX(scale,libbcmath.MAX(n1.n_scale,n2.n_scale)));pval=libbcmath._bc_rec_mul(n1,len1,n2,len2,full_scale);pval.n_sign=(n1.n_sign==n2.n_sign?libbcmath.PLUS:libbcmath.MINUS);pval.n_len=len2+len1+1-full_scale;pval.n_scale=prod_scale;libbcmath._bc_rm_leading_zeros(pval);if(libbcmath.bc_is_zero(pval)){pval.n_sign=libbcmath.PLUS;}
return pval;},new_sub_num:function(length,scale,value){var temp=new libbcmath.bc_num();temp.n_sign=libbcmath.PLUS;temp.n_len=length;temp.n_scale=scale;temp.n_value=value;return temp;},_bc_simp_mul:function(n1,n1len,n2,n2len,full_scale){var prod;var n1ptr,n2ptr,pvptr;var n1end,n2end;var indx,sum,prodlen;prodlen=n1len+n2len+1;prod=libbcmath.bc_new_num(prodlen,0);n1end=n1len-1;n2end=n2len-1;pvptr=prodlen-1;sum=0;for(indx=0;indx<prodlen-1;indx++){n1ptr=n1end-libbcmath.MAX(0,indx-n2len+1);n2ptr=n2end-libbcmath.MIN(indx,n2len-1);while((n1ptr>=0)&&(n2ptr<=n2end)){sum+=n1.n_value[n1ptr--]*n2.n_value[n2ptr++];}
prod.n_value[pvptr--]=Math.floor(sum%libbcmath.BASE);sum=Math.floor(sum/libbcmath.BASE);}
prod.n_value[pvptr]=sum;return prod;},_bc_shift_addsub:function(accum,val,shift,sub){var accp,valp;var count,carry;count=val.n_len;if(val.n_value[0]===0){count--;}
if(accum.n_len+accum.n_scale<shift+count){throw new Error("len + scale < shift + count");}
accp=accum.n_len+accum.n_scale-shift-1;valp=val.n_len=1;carry=0;if(sub){while(count--){accum.n_value[accp]-=val.n_value[valp--]+carry;if(accum.n_value[accp]<0){carry=1;accum.n_value[accp--]+=libbcmath.BASE;}else{carry=0;accp--;}}
while(carry){accum.n_value[accp]-=carry;if(accum.n_value[accp]<0){accum.n_value[accp--]+=libbcmath.BASE;}else{carry=0;}}}else{while(count--){accum.n_value[accp]+=val.n_value[valp--]+carry;if(accum.n_value[accp]>(libbcmath.BASE-1)){carry=1;accum.n_value[accp--]-=libbcmath.BASE;}else{carry=0;accp--;}}
while(carry){accum.n_value[accp]+=carry;if(accum.n_value[accp]>(libbcmath.BASE-1)){accum.n_value[accp--]-=libbcmath.BASE;}else{carry=0;}}}
return true;},_bc_rec_mul:function(u,ulen,v,vlen,full_scale){var prod;var u0,u1,v0,v1;var u0len,v0len;var m1,m2,m3,d1,d2;var n,prodlen,m1zero;var d1len,d2len;if((ulen+vlen)<libbcmath.MUL_BASE_DIGITS||ulen<libbcmath.MUL_SMALL_DIGITS||vlen<libbcmath.MUL_SMALL_DIGITS){return libbcmath._bc_simp_mul(u,ulen,v,vlen,full_scale);}
n=Math.floor((libbcmath.MAX(ulen,vlen)+1)/2);if(ulen<n){u1=libbcmath.bc_init_num();u0=libbcmath.new_sub_num(ulen,0,u.n_value);}else{u1=libbcmath.new_sub_num(ulen-n,0,u.n_value);u0=libbcmath.new_sub_num(n,0,u.n_value+ulen-n);}
if(vlen<n){v1=libbcmath.bc_init_num();v0=libbcmath.new_sub_num(vlen,0,v.n_value);}else{v1=libbcmath.new_sub_num(vlen-n,0,v.n_value);v0=libbcmath.new_sub_num(n,0,v.n_value+vlen-n);}
libbcmath._bc_rm_leading_zeros(u1);libbcmath._bc_rm_leading_zeros(u0);u0len=u0.n_len;libbcmath._bc_rm_leading_zeros(v1);libbcmath._bc_rm_leading_zeros(v0);v0len=v0.n_len;m1zero=libbcmath.bc_is_zero(u1)||libbcmath.bc_is_zero(v1);d1=libbcmath.bc_init_num();d2=libbcmath.bc_init_num();d1=libbcmath.bc_sub(u1,u0,0);d1len=d1.n_len;d2=libbcmath.bc_sub(v0,v1,0);d2len=d2.n_len;if(m1zero){m1=libbcmath.bc_init_num();}else{m1=libbcmath._bc_rec_mul(u1,u1.n_len,v1,v1.n_len,0);}
if(libbcmath.bc_is_zero(d1)||libbcmath.bc_is_zero(d2)){m2=libbcmath.bc_init_num();}else{m2=libbcmath._bc_rec_mul(d1,d1len,d2,d2len,0);}
if(libbcmath.bc_is_zero(u0)||libbcmath.bc_is_zero(v0)){m3=libbcmath.bc_init_num();}else{m3=libbcmath._bc_rec_mul(u0,u0.n_len,v0,v0.n_len,0);}
prodlen=ulen+vlen+1;prod=libbcmath.bc_new_num(prodlen,0);if(!m1zero){libbcmath._bc_shift_addsub(prod,m1,2*n,0);libbcmath._bc_shift_addsub(prod,m1,n,0);}
libbcmath._bc_shift_addsub(prod,m3,n,0);libbcmath._bc_shift_addsub(prod,m3,0,0);libbcmath._bc_shift_addsub(prod,m2,n,d1.n_sign!=d2.n_sign);return prod;},_bc_do_compare:function(n1,n2,use_sign,ignore_last){var n1ptr,n2ptr;var count;if(use_sign&&(n1.n_sign!=n2.n_sign)){if(n1.n_sign==libbcmath.PLUS){return(1);}else{return(-1);}}
if(n1.n_len!=n2.n_len){if(n1.n_len>n2.n_len){if(!use_sign||(n1.n_sign==libbcmath.PLUS)){return(1);}else{return(-1);}}else{if(!use_sign||(n1.n_sign==libbcmath.PLUS)){return(-1);}else{return(1);}}}
count=n1.n_len+Math.min(n1.n_scale,n2.n_scale);n1ptr=0;n2ptr=0;while((count>0)&&(n1.n_value[n1ptr]==n2.n_value[n2ptr])){n1ptr++;n2ptr++;count--;}
if(ignore_last&&(count==1)&&(n1.n_scale==n2.n_scale)){return(0);}
if(count!==0){if(n1.n_value[n1ptr]>n2.n_value[n2ptr]){if(!use_sign||n1.n_sign==libbcmath.PLUS){return(1);}else{return(-1);}}else{if(!use_sign||n1.n_sign==libbcmath.PLUS){return(-1);}else{return(1);}}}
if(n1.n_scale!=n2.n_scale){if(n1.n_scale>n2.n_scale){for(count=(n1.n_scale-n2.n_scale);count>0;count--){if(n1.n_value[n1ptr++]!==0){if(!use_sign||n1.n_sign==libbcmath.PLUS){return(1);}else{return(-1);}}}}else{for(count=(n2.n_scale-n1.n_scale);count>0;count--){if(n2.n_value[n2ptr++]!==0){if(!use_sign||n1.n_sign==libbcmath.PLUS){return(-1);}else{return(1);}}}}}
return(0);},bc_sub:function(n1,n2,scale_min){var diff;var cmp_res,res_scale;if(n1.n_sign!=n2.n_sign){diff=libbcmath._bc_do_add(n1,n2,scale_min);diff.n_sign=n1.n_sign;}else{cmp_res=libbcmath._bc_do_compare(n1,n2,false,false);switch(cmp_res){case-1:diff=libbcmath._bc_do_sub(n2,n1,scale_min);diff.n_sign=(n2.n_sign==libbcmath.PLUS?libbcmath.MINUS:libbcmath.PLUS);break;case 0:res_scale=libbcmath.MAX(scale_min,libbcmath.MAX(n1.n_scale,n2.n_scale));diff=libbcmath.bc_new_num(1,res_scale);libbcmath.memset(diff.n_value,0,0,res_scale+1);break;case 1:diff=libbcmath._bc_do_sub(n1,n2,scale_min);diff.n_sign=n1.n_sign;break;}}
return diff;},_bc_do_add:function(n1,n2,scale_min){var sum;var sum_scale,sum_digits;var n1ptr,n2ptr,sumptr;var carry,n1bytes,n2bytes;var tmp;sum_scale=libbcmath.MAX(n1.n_scale,n2.n_scale);sum_digits=libbcmath.MAX(n1.n_len,n2.n_len)+1;sum=libbcmath.bc_new_num(sum_digits,libbcmath.MAX(sum_scale,scale_min));n1bytes=n1.n_scale;n2bytes=n2.n_scale;n1ptr=(n1.n_len+n1bytes-1);n2ptr=(n2.n_len+n2bytes-1);sumptr=(sum_scale+sum_digits-1);if(n1bytes!=n2bytes){if(n1bytes>n2bytes){while(n1bytes>n2bytes){sum.n_value[sumptr--]=n1.n_value[n1ptr--];n1bytes--;}}else{while(n2bytes>n1bytes){sum.n_value[sumptr--]=n2.n_value[n2ptr--];n2bytes--;}}}
n1bytes+=n1.n_len;n2bytes+=n2.n_len;carry=0;while((n1bytes>0)&&(n2bytes>0)){tmp=n1.n_value[n1ptr--]+n2.n_value[n2ptr--]+carry;if(tmp>=libbcmath.BASE){carry=1;tmp-=libbcmath.BASE;}else{carry=0;}
sum.n_value[sumptr]=tmp;sumptr--;n1bytes--;n2bytes--;}
if(n1bytes===0){while(n2bytes-->0){tmp=n2.n_value[n2ptr--]+carry;if(tmp>=libbcmath.BASE){carry=1;tmp-=libbcmath.BASE;}else{carry=0;}
sum.n_value[sumptr--]=tmp;}}else{while(n1bytes-->0){tmp=n1.n_value[n1ptr--]+carry;if(tmp>=libbcmath.BASE){carry=1;tmp-=libbcmath.BASE;}else{carry=0;}
sum.n_value[sumptr--]=tmp;}}
if(carry==1){sum.n_value[sumptr]+=1;}
libbcmath._bc_rm_leading_zeros(sum);return sum;},_bc_do_sub:function(n1,n2,scale_min){var diff;var diff_scale,diff_len;var min_scale,min_len;var n1ptr,n2ptr,diffptr;var borrow,count,val;diff_len=libbcmath.MAX(n1.n_len,n2.n_len);diff_scale=libbcmath.MAX(n1.n_scale,n2.n_scale);min_len=libbcmath.MIN(n1.n_len,n2.n_len);min_scale=libbcmath.MIN(n1.n_scale,n2.n_scale);diff=libbcmath.bc_new_num(diff_len,libbcmath.MAX(diff_scale,scale_min));n1ptr=(n1.n_len+n1.n_scale-1);n2ptr=(n2.n_len+n2.n_scale-1);diffptr=(diff_len+diff_scale-1);borrow=0;if(n1.n_scale!=min_scale){for(count=n1.n_scale-min_scale;count>0;count--){diff.n_value[diffptr--]=n1.n_value[n1ptr--];}}else{for(count=n2.n_scale-min_scale;count>0;count--){val=0-n2.n_value[n2ptr--]-borrow;if(val<0){val+=libbcmath.BASE;borrow=1;}else{borrow=0;}
diff.n_value[diffptr--]=val;}}
for(count=0;count<min_len+min_scale;count++){val=n1.n_value[n1ptr--]-n2.n_value[n2ptr--]-borrow;if(val<0){val+=libbcmath.BASE;borrow=1;}else{borrow=0;}
diff.n_value[diffptr--]=val;}
if(diff_len!=min_len){for(count=diff_len-min_len;count>0;count--){val=n1.n_value[n1ptr--]-borrow;if(val<0){val+=libbcmath.BASE;borrow=1;}else{borrow=0;}
diff.n_value[diffptr--]=val;}}
libbcmath._bc_rm_leading_zeros(diff);return diff;},bc_new_num:function(length,scale){var temp;temp=new libbcmath.bc_num();temp.n_sign=libbcmath.PLUS;temp.n_len=length;temp.n_scale=scale;temp.n_value=libbcmath.safe_emalloc(1,length+scale,0);libbcmath.memset(temp.n_value,0,0,length+scale);return temp;},safe_emalloc:function(size,len,extra){return Array((size*len)+extra);},bc_init_num:function(){return new libbcmath.bc_new_num(1,0);},_bc_rm_leading_zeros:function(num){while((num.n_value[0]===0)&&(num.n_len>1)){num.n_value.shift();num.n_len--;}},php_str2num:function(str){var p;p=str.indexOf('.');if(p==-1){return libbcmath.bc_str2num(str,0);}else{return libbcmath.bc_str2num(str,(str.length-p));}},CH_VAL:function(c){return c-'0';},BCD_CHAR:function(d){return d+'0';},isdigit:function(c){return(isNaN(parseInt(c,10))?false:true);},bc_str2num:function(str_in,scale){var str,num,ptr,digits,strscale,zero_int,nptr;str=str_in.split('');ptr=0;digits=0;strscale=0;zero_int=false;if((str[ptr]==='+')||(str[ptr]==='-')){ptr++;}
while(str[ptr]==='0'){ptr++;}
while((str[ptr])%1===0){ptr++;digits++;}
if(str[ptr]==='.'){ptr++;}
while((str[ptr])%1===0){ptr++;strscale++;}
if((str[ptr])||(digits+strscale===0)){return libbcmath.bc_init_num();}
strscale=libbcmath.MIN(strscale,scale);if(digits===0){zero_int=true;digits=1;}
num=libbcmath.bc_new_num(digits,strscale);ptr=0;if(str[ptr]==='-'){num.n_sign=libbcmath.MINUS;ptr++;}else{num.n_sign=libbcmath.PLUS;if(str[ptr]==='+'){ptr++;}}
while(str[ptr]==='0'){ptr++;}
nptr=0;if(zero_int){num.n_value[nptr++]=0;digits=0;}
for(;digits>0;digits--){num.n_value[nptr++]=libbcmath.CH_VAL(str[ptr++]);}
if(strscale>0){ptr++;for(;strscale>0;strscale--){num.n_value[nptr++]=libbcmath.CH_VAL(str[ptr++]);}}
return num;},cint:function(v){if(typeof v==='undefined'){v=0;}
var x=parseInt(v,10);if(isNaN(x)){x=0;}
return x;},MIN:function(a,b){return((a>b)?b:a);},MAX:function(a,b){return((a>b)?a:b);},ODD:function(a){return(a&1);},memset:function(r,ptr,chr,len){var i;for(i=0;i<len;i++){r[ptr+i]=chr;}},memcpy:function(dest,ptr,src,srcptr,len){var i;for(i=0;i<len;i++){dest[ptr+i]=src[srcptr+i];}
return true;},bc_is_zero:function(num){var count;var nptr;count=num.n_len+num.n_scale;nptr=0;while((count>0)&&(num.n_value[nptr++]===0)){count--;}
if(count!==0){return false;}else{return true;}},bc_out_of_memory:function(){throw new Error("(BC) Out of memory");}};return libbcmath;}
function each(arr){this.php_js=this.php_js||{};this.php_js.pointers=this.php_js.pointers||[];var indexOf=function(value){for(var i=0,length=this.length;i<length;i++){if(this[i]===value){return i;}}
return-1;};var pointers=this.php_js.pointers;if(!pointers.indexOf){pointers.indexOf=indexOf;}
if(pointers.indexOf(arr)===-1){pointers.push(arr,0);}
var arrpos=pointers.indexOf(arr);var cursor=pointers[arrpos+1];var pos=0;if(Object.prototype.toString.call(arr)!=='[object Array]'){var ct=0;for(var k in arr){if(ct===cursor){pointers[arrpos+1]+=1;if(each.returnArrayOnly){return[k,arr[k]];}else{return{1:arr[k],value:arr[k],0:k,key:k};}}
ct++;}
return false;}
if(arr.length===0||cursor===arr.length){return false;}
pos=cursor;pointers[arrpos+1]+=1;if(each.returnArrayOnly){return[pos,arr[pos]];}else{return{1:arr[pos],value:arr[pos],0:pos,key:pos};}}
function count(mixed_var,mode){var key,cnt=0;if(mixed_var===null||typeof mixed_var==='undefined'){return 0;}else if(mixed_var.constructor!==Array&&mixed_var.constructor!==Object){return 1;}
if(mode==='COUNT_RECURSIVE'){mode=1;}
if(mode!=1){mode=0;}
for(key in mixed_var){if(mixed_var.hasOwnProperty(key)){cnt++;if(mode==1&&mixed_var[key]&&(mixed_var[key].constructor===Array||mixed_var[key].constructor===Object)){cnt+=this.count(mixed_var[key],1);}}}
return cnt;}
function date(format,timestamp){var that=this,jsdate,f,formatChr=/\\?([a-z])/gi,formatChrCb,_pad=function(n,c){n=n.toString();return n.length<c?_pad('0'+n,c,'0'):n;},txt_words=["Sun","Mon","Tues","Wednes","Thurs","Fri","Satur","January","February","March","April","May","June","July","August","September","October","November","December"];formatChrCb=function(t,s){return f[t]?f[t]():s;};f={d:function(){return _pad(f.j(),2);},D:function(){return f.l().slice(0,3);},j:function(){return jsdate.getDate();},l:function(){return txt_words[f.w()]+'day';},N:function(){return f.w()||7;},S:function(){var j=f.j()
i=j%10;if(i<=3&&parseInt((j%100)/10)==1)i=0;return['st','nd','rd'][i-1]||'th';},w:function(){return jsdate.getDay();},z:function(){var a=new Date(f.Y(),f.n()-1,f.j()),b=new Date(f.Y(),0,1);return Math.round((a-b)/864e5);},W:function(){var a=new Date(f.Y(),f.n()-1,f.j()-f.N()+3),b=new Date(a.getFullYear(),0,4);return _pad(1+Math.round((a-b)/864e5/7),2);},F:function(){return txt_words[6+f.n()];},m:function(){return _pad(f.n(),2);},M:function(){return f.F().slice(0,3);},n:function(){return jsdate.getMonth()+1;},t:function(){return(new Date(f.Y(),f.n(),0)).getDate();},L:function(){var j=f.Y();return j%4===0&j%100!==0|j%400===0;},o:function(){var n=f.n(),W=f.W(),Y=f.Y();return Y+(n===12&&W<9?1:n===1&&W>9?-1:0);},Y:function(){return jsdate.getFullYear();},y:function(){return f.Y().toString().slice(-2);},a:function(){return jsdate.getHours()>11?"pm":"am";},A:function(){return f.a().toUpperCase();},B:function(){var H=jsdate.getUTCHours()*36e2,i=jsdate.getUTCMinutes()*60,s=jsdate.getUTCSeconds();return _pad(Math.floor((H+i+s+36e2)/86.4)%1e3,3);},g:function(){return f.G()%12||12;},G:function(){return jsdate.getHours();},h:function(){return _pad(f.g(),2);},H:function(){return _pad(f.G(),2);},i:function(){return _pad(jsdate.getMinutes(),2);},s:function(){return _pad(jsdate.getSeconds(),2);},u:function(){return _pad(jsdate.getMilliseconds()*1000,6);},e:function(){throw'Not supported (see source code of date() for timezone on how to add support)';},I:function(){var a=new Date(f.Y(),0),c=Date.UTC(f.Y(),0),b=new Date(f.Y(),6),d=Date.UTC(f.Y(),6);return((a-c)!==(b-d))?1:0;},O:function(){var tzo=jsdate.getTimezoneOffset(),a=Math.abs(tzo);return(tzo>0?"-":"+")+_pad(Math.floor(a/60)*100+a%60,4);},P:function(){var O=f.O();return(O.substr(0,3)+":"+O.substr(3,2));},T:function(){return'UTC';},Z:function(){return-jsdate.getTimezoneOffset()*60;},c:function(){return'Y-m-d\\TH:i:sP'.replace(formatChr,formatChrCb);},r:function(){return'D, d M Y H:i:s O'.replace(formatChr,formatChrCb);},U:function(){return jsdate/1000|0;}};this.date=function(format,timestamp){that=this;jsdate=(timestamp===undefined?new Date():(timestamp instanceof Date)?new Date(timestamp):new Date(timestamp*1000));return format.replace(formatChr,formatChrCb);};return this.date(format,timestamp);}
function json_decode(str_json){var json=this.window.JSON;if(typeof json==='object'&&typeof json.parse==='function'){try{return json.parse(str_json);}catch(err){if(!(err instanceof SyntaxError)){throw new Error('Unexpected error type in json_decode()');}
this.php_js=this.php_js||{};this.php_js.last_error_json=4;return null;}}
var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;var j;var text=str_json;cx.lastIndex=0;if(cx.test(text)){text=text.replace(cx,function(a){return'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);});}
if((/^[\],:{}\s]*$/).test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,''))){j=eval('('+text+')');return j;}
this.php_js=this.php_js||{};this.php_js.last_error_json=4;return null;}
function json_encode(mixed_val){var retVal,json=this.window.JSON;try{if(typeof json==='object'&&typeof json.stringify==='function'){retVal=json.stringify(mixed_val);if(retVal===undefined){throw new SyntaxError('json_encode');}
return retVal;}
var value=mixed_val;var quote=function(string){var escapable=/[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;var meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);})+'"':'"'+string+'"';};var str=function(key,holder){var gap='';var indent='    ';var i=0;var k='';var v='';var length=0;var mind=gap;var partial=[];var value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key);}
switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null';}
if((this.PHPJS_Resource&&value instanceof this.PHPJS_Resource)||(window.PHPJS_Resource&&value instanceof window.PHPJS_Resource)){throw new SyntaxError('json_encode');}
gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null';}
v=partial.length===0?'[]':gap?'[\n'+gap+partial.join(',\n'+gap)+'\n'+mind+']':'['+partial.join(',')+']';gap=mind;return v;}
for(k in value){if(Object.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}
v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+mind+'}':'{'+partial.join(',')+'}';gap=mind;return v;case'undefined':case'function':default:throw new SyntaxError('json_encode');}};return str('',{'':value});}catch(err){if(!(err instanceof SyntaxError)){throw new Error('Unexpected error type in json_encode()');}
this.php_js=this.php_js||{};this.php_js.last_error_json=4;return null;}}
function rand(min,max){var argc=arguments.length;if(argc===0){min=0;max=2147483647;}else if(argc===1){throw new Error('Warning: rand() expects exactly 2 parameters, 1 given');}
return Math.floor(Math.random()*(max-min+1))+min;}
function uniqid(prefix,more_entropy){if(typeof prefix==='undefined'){prefix="";}
var retId;var formatSeed=function(seed,reqWidth){seed=parseInt(seed,10).toString(16);if(reqWidth<seed.length){return seed.slice(seed.length-reqWidth);}
if(reqWidth>seed.length){return Array(1+(reqWidth-seed.length)).join('0')+seed;}
return seed;};if(!this.php_js){this.php_js={};}
if(!this.php_js.uniqidSeed){this.php_js.uniqidSeed=Math.floor(Math.random()*0x75bcd15);}
this.php_js.uniqidSeed++;retId=prefix;retId+=formatSeed(parseInt(new Date().getTime()/1000,10),8);retId+=formatSeed(this.php_js.uniqidSeed,5);if(more_entropy){retId+=(Math.random()*10).toFixed(8).toString();}
return retId;}
function echo(){var arg='',argc=arguments.length,argv=arguments,i=0,holder,win=this.window,d=win.document,ns_xhtml='http://www.w3.org/1999/xhtml',ns_xul='http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul';var stringToDOM=function(str,parent,ns,container){var extraNSs='';if(ns===ns_xul){extraNSs=' xmlns:html="'+ns_xhtml+'"';}
var stringContainer='<'+container+' xmlns="'+ns+'"'+extraNSs+'>'+str+'</'+container+'>';var dils=win.DOMImplementationLS,dp=win.DOMParser,ax=win.ActiveXObject;if(dils&&dils.createLSInput&&dils.createLSParser){var lsInput=dils.createLSInput();lsInput.stringData=stringContainer;var lsParser=dils.createLSParser(1,null);return lsParser.parse(lsInput).firstChild;}else if(dp){try{var fc=new dp().parseFromString(stringContainer,'text/xml');if(fc&&fc.documentElement&&fc.documentElement.localName!=='parsererror'&&fc.documentElement.namespaceURI!=='http://www.mozilla.org/newlayout/xml/parsererror.xml'){return fc.documentElement.firstChild;}}catch(e){}}else if(ax){var axo=new ax('MSXML2.DOMDocument');axo.loadXML(str);return axo.documentElement;}
if(d.createElementNS&&(d.documentElement.namespaceURI||d.documentElement.nodeName.toLowerCase()!=='html'||(d.contentType&&d.contentType!=='text/html'))){holder=d.createElementNS(ns,container);}else{holder=d.createElement(container);}
holder.innerHTML=str;while(holder.firstChild){parent.appendChild(holder.firstChild);}
return false;};var ieFix=function(node){if(node.nodeType===1){var newNode=d.createElement(node.nodeName);var i,len;if(node.attributes&&node.attributes.length>0){for(i=0,len=node.attributes.length;i<len;i++){newNode.setAttribute(node.attributes[i].nodeName,node.getAttribute(node.attributes[i].nodeName));}}
if(node.childNodes&&node.childNodes.length>0){for(i=0,len=node.childNodes.length;i<len;i++){newNode.appendChild(ieFix(node.childNodes[i]));}}
return newNode;}else{return d.createTextNode(node.nodeValue);}};var replacer=function(s,m1,m2){if(m1!=='\\'){return m1+eval(m2);}else{return s;}};this.php_js=this.php_js||{};var phpjs=this.php_js,ini=phpjs.ini,obs=phpjs.obs;for(i=0;i<argc;i++){arg=argv[i];if(ini&&ini['phpjs.echo_embedded_vars']){arg=arg.replace(/(.?)\{?\$(\w*?\}|\w*)/g,replacer);}
if(!phpjs.flushing&&obs&&obs.length){obs[obs.length-1].buffer+=arg;continue;}
if(d.appendChild){if(d.body){if(win.navigator.appName==='Microsoft Internet Explorer'){d.body.appendChild(stringToDOM(ieFix(arg)));}else{var unappendedLeft=stringToDOM(arg,d.body,ns_xhtml,'div').cloneNode(true);if(unappendedLeft){d.body.appendChild(unappendedLeft);}}}else{d.documentElement.appendChild(stringToDOM(arg,d.documentElement,ns_xul,'description'));}}else if(d.write){d.write(arg);}}}
function explode(delimiter,string,limit){if(arguments.length<2||typeof delimiter==='undefined'||typeof string==='undefined')return null;if(delimiter===''||delimiter===false||delimiter===null)return false;if(typeof delimiter==='function'||typeof delimiter==='object'||typeof string==='function'||typeof string==='object'){return{0:''};}
if(delimiter===true)delimiter='1';delimiter+='';string+='';var s=string.split(delimiter);if(typeof limit==='undefined')return s;if(limit===0)limit=1;if(limit>0){if(limit>=s.length)return s;return s.slice(0,limit-1).concat([s.slice(limit-1).join(delimiter)]);}
if(-limit>=s.length)return[];s.splice(s.length+limit);return s;}
function implode(glue,pieces){var i='',retVal='',tGlue='';if(arguments.length===1){pieces=glue;glue='';}
if(typeof pieces==='object'){if(Object.prototype.toString.call(pieces)==='[object Array]'){return pieces.join(glue);}
for(i in pieces){retVal+=tGlue+pieces[i];tGlue=glue;}
return retVal;}
return pieces;}
function ltrim(str,charlist){charlist=!charlist?' \\s\u00A0':(charlist+'').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g,'$1');var re=new RegExp('^['+charlist+']+','g');return(str+'').replace(re,'');}
function number_format(number,decimals,dec_point,thousands_sep){number=(number+'').replace(/[^0-9+\-Ee.]/g,'');var n=!isFinite(+number)?0:+number,prec=!isFinite(+decimals)?0:Math.abs(decimals),sep=(typeof thousands_sep==='undefined')?',':thousands_sep,dec=(typeof dec_point==='undefined')?'.':dec_point,s='',toFixedFix=function(n,prec){var k=Math.pow(10,prec);return''+Math.round(n*k)/k;};s=(prec?toFixedFix(n,prec):''+Math.round(n)).split('.');if(s[0].length>3){s[0]=s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,sep);}
if((s[1]||'').length<prec){s[1]=s[1]||'';s[1]+=new Array(prec-s[1].length+1).join('0');}
return s.join(dec);}
function rtrim(str,charlist){charlist=!charlist?' \\s\u00A0':(charlist+'').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g,'\\$1');var re=new RegExp('['+charlist+']+$','g');return(str+'').replace(re,'');}
function split(delimiter,string){return this.explode(delimiter,string);}
function sprintf(){var regex=/%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuideEfFgG])/g;var a=arguments,i=0,format=a[i++];var pad=function(str,len,chr,leftJustify){if(!chr){chr=' ';}
var padding=(str.length>=len)?'':Array(1+len-str.length>>>0).join(chr);return leftJustify?str+padding:padding+str;};var justify=function(value,prefix,leftJustify,minWidth,zeroPad,customPadChar){var diff=minWidth-value.length;if(diff>0){if(leftJustify||!zeroPad){value=pad(value,minWidth,customPadChar,leftJustify);}else{value=value.slice(0,prefix.length)+pad('',diff,'0',true)+value.slice(prefix.length);}}
return value;};var formatBaseX=function(value,base,prefix,leftJustify,minWidth,precision,zeroPad){var number=value>>>0;prefix=prefix&&number&&{'2':'0b','8':'0','16':'0x'}[base]||'';value=prefix+pad(number.toString(base),precision||0,'0',false);return justify(value,prefix,leftJustify,minWidth,zeroPad);};var formatString=function(value,leftJustify,minWidth,precision,zeroPad,customPadChar){if(precision!=null){value=value.slice(0,precision);}
return justify(value,'',leftJustify,minWidth,zeroPad,customPadChar);};var doFormat=function(substring,valueIndex,flags,minWidth,_,precision,type){var number;var prefix;var method;var textTransform;var value;if(substring==='%%'){return'%';}
var leftJustify=false,positivePrefix='',zeroPad=false,prefixBaseX=false,customPadChar=' ';var flagsl=flags.length;for(var j=0;flags&&j<flagsl;j++){switch(flags.charAt(j)){case' ':positivePrefix=' ';break;case'+':positivePrefix='+';break;case'-':leftJustify=true;break;case"'":customPadChar=flags.charAt(j+1);break;case'0':zeroPad=true;break;case'#':prefixBaseX=true;break;}}
if(!minWidth){minWidth=0;}else if(minWidth==='*'){minWidth=+a[i++];}else if(minWidth.charAt(0)=='*'){minWidth=+a[minWidth.slice(1,-1)];}else{minWidth=+minWidth;}
if(minWidth<0){minWidth=-minWidth;leftJustify=true;}
if(!isFinite(minWidth)){throw new Error('sprintf: (minimum-)width must be finite');}
if(!precision){precision='fFeE'.indexOf(type)>-1?6:(type==='d')?0:undefined;}else if(precision==='*'){precision=+a[i++];}else if(precision.charAt(0)=='*'){precision=+a[precision.slice(1,-1)];}else{precision=+precision;}
value=valueIndex?a[valueIndex.slice(0,-1)]:a[i++];switch(type){case's':return formatString(String(value),leftJustify,minWidth,precision,zeroPad,customPadChar);case'c':return formatString(String.fromCharCode(+value),leftJustify,minWidth,precision,zeroPad);case'b':return formatBaseX(value,2,prefixBaseX,leftJustify,minWidth,precision,zeroPad);case'o':return formatBaseX(value,8,prefixBaseX,leftJustify,minWidth,precision,zeroPad);case'x':return formatBaseX(value,16,prefixBaseX,leftJustify,minWidth,precision,zeroPad);case'X':return formatBaseX(value,16,prefixBaseX,leftJustify,minWidth,precision,zeroPad).toUpperCase();case'u':return formatBaseX(value,10,prefixBaseX,leftJustify,minWidth,precision,zeroPad);case'i':case'd':number=+value||0;number=Math.round(number-number%1);prefix=number<0?'-':positivePrefix;value=prefix+pad(String(Math.abs(number)),precision,'0',false);return justify(value,prefix,leftJustify,minWidth,zeroPad);case'e':case'E':case'f':case'F':case'g':case'G':number=+value;prefix=number<0?'-':positivePrefix;method=['toExponential','toFixed','toPrecision']['efg'.indexOf(type.toLowerCase())];textTransform=['toString','toUpperCase']['eEfFgG'.indexOf(type)%2];value=prefix+Math.abs(number)[method](precision);return justify(value,prefix,leftJustify,minWidth,zeroPad)[textTransform]();default:return substring;}};return format.replace(regex,doFormat);}
function str_replace(search,replace,subject,count){var i=0,j=0,temp='',repl='',sl=0,fl=0,f=[].concat(search),r=[].concat(replace),s=subject,ra=Object.prototype.toString.call(r)==='[object Array]',sa=Object.prototype.toString.call(s)==='[object Array]';s=[].concat(s);if(count){this.window[count]=0;}
for(i=0,sl=s.length;i<sl;i++){if(s[i]===''){continue;}
for(j=0,fl=f.length;j<fl;j++){temp=s[i]+'';repl=ra?(r[j]!==undefined?r[j]:''):r[0];s[i]=(temp).split(f[j]).join(repl);if(count&&s[i]!==temp){this.window[count]+=(temp.length-s[i].length)/f[j].length;}}}
return sa?s:s[0];}
function str_split(string,split_length){if(split_length===null){split_length=1;}
if(string===null||split_length<1){return false;}
string+='';var chunks=[],pos=0,len=string.length;while(pos<len){chunks.push(string.slice(pos,pos+=split_length));}
return chunks;}
function stripos(f_haystack,f_needle,f_offset){var haystack=(f_haystack+'').toLowerCase();var needle=(f_needle+'').toLowerCase();var index=0;if((index=haystack.indexOf(needle,f_offset))!==-1){return index;}
return false;}
function stristr(haystack,needle,bool){var pos=0;haystack+='';pos=haystack.toLowerCase().indexOf((needle+'').toLowerCase());if(pos==-1){return false;}else{if(bool){return haystack.substr(0,pos);}else{return haystack.slice(pos);}}}
function strlen(string){var str=string+'';var i=0,chr='',lgth=0;if(!this.php_js||!this.php_js.ini||!this.php_js.ini['unicode.semantics']||this.php_js.ini['unicode.semantics'].local_value.toLowerCase()!=='on'){return string.length;}
var getWholeChar=function(str,i){var code=str.charCodeAt(i);var next='',prev='';if(0xD800<=code&&code<=0xDBFF){if(str.length<=(i+1)){throw'High surrogate without following low surrogate';}
next=str.charCodeAt(i+1);if(0xDC00>next||next>0xDFFF){throw'High surrogate without following low surrogate';}
return str.charAt(i)+str.charAt(i+1);}else if(0xDC00<=code&&code<=0xDFFF){if(i===0){throw'Low surrogate without preceding high surrogate';}
prev=str.charCodeAt(i-1);if(0xD800>prev||prev>0xDBFF){throw'Low surrogate without preceding high surrogate';}
return false;}
return str.charAt(i);};for(i=0,lgth=0;i<str.length;i++){if((chr=getWholeChar(str,i))===false){continue;}
lgth++;}
return lgth;}
function strripos(haystack,needle,offset){haystack=(haystack+'').toLowerCase();needle=(needle+'').toLowerCase();var i=-1;if(offset){i=(haystack+'').slice(offset).lastIndexOf(needle);if(i!==-1){i+=offset;}}else{i=(haystack+'').lastIndexOf(needle);}
return i>=0?i:false;}
function strtolower(str){return(str+'').toLowerCase();}
function strtoupper(str){return(str+'').toUpperCase();}
function substr(str,start,len){var i=0,allBMP=true,es=0,el=0,se=0,ret='';str+='';var end=str.length;this.php_js=this.php_js||{};this.php_js.ini=this.php_js.ini||{};switch((this.php_js.ini['unicode.semantics']&&this.php_js.ini['unicode.semantics'].local_value.toLowerCase())){case'on':for(i=0;i<str.length;i++){if(/[\uD800-\uDBFF]/.test(str.charAt(i))&&/[\uDC00-\uDFFF]/.test(str.charAt(i+1))){allBMP=false;break;}}
if(!allBMP){if(start<0){for(i=end-1,es=(start+=end);i>=es;i--){if(/[\uDC00-\uDFFF]/.test(str.charAt(i))&&/[\uD800-\uDBFF]/.test(str.charAt(i-1))){start--;es--;}}}else{var surrogatePairs=/[\uD800-\uDBFF][\uDC00-\uDFFF]/g;while((surrogatePairs.exec(str))!=null){var li=surrogatePairs.lastIndex;if(li-2<start){start++;}else{break;}}}
if(start>=end||start<0){return false;}
if(len<0){for(i=end-1,el=(end+=len);i>=el;i--){if(/[\uDC00-\uDFFF]/.test(str.charAt(i))&&/[\uD800-\uDBFF]/.test(str.charAt(i-1))){end--;el--;}}
if(start>end){return false;}
return str.slice(start,end);}else{se=start+len;for(i=start;i<se;i++){ret+=str.charAt(i);if(/[\uD800-\uDBFF]/.test(str.charAt(i))&&/[\uDC00-\uDFFF]/.test(str.charAt(i+1))){se++;}}
return ret;}
break;}
case'off':default:if(start<0){start+=end;}
end=typeof len==='undefined'?end:(len<0?len+end:len+start);return start>=str.length||start<0||start>end?!1:str.slice(start,end);}
return undefined;}
function trim(str,charlist){var whitespace,l=0,i=0;str+='';if(!charlist){whitespace=" \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";}else{charlist+='';whitespace=charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g,'$1');}
l=str.length;for(i=0;i<l;i++){if(whitespace.indexOf(str.charAt(i))===-1){str=str.substring(i);break;}}
l=str.length;for(i=l-1;i>=0;i--){if(whitespace.indexOf(str.charAt(i))===-1){str=str.substring(0,i+1);break;}}
return whitespace.indexOf(str.charAt(0))===-1?str:'';}
function wordwrap(str,int_width,str_break,cut){var m=((arguments.length>=2)?arguments[1]:75);var b=((arguments.length>=3)?arguments[2]:"\n");var c=((arguments.length>=4)?arguments[3]:false);var i,j,l,s,r;str+='';if(m<1){return str;}
for(i=-1,l=(r=str.split(/\r\n|\n|\r/)).length;++i<l;r[i]+=s){for(s=r[i],r[i]="";s.length>m;r[i]+=s.slice(0,j)+((s=s.slice(j)).length?b:"")){j=c==2||(j=s.slice(0,m+1).match(/\S*(\s)?$/))[1]?m:j.input.length-j[0].length||c==1&&m||j.input.length+(j=s.slice(m).match(/^\S*/)).input.length;}}
return r.join("\n");}
function base64_decode(data){var b64="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var o1,o2,o3,h1,h2,h3,h4,bits,i=0,ac=0,dec="",tmp_arr=[];if(!data){return data;}
data+='';do{h1=b64.indexOf(data.charAt(i++));h2=b64.indexOf(data.charAt(i++));h3=b64.indexOf(data.charAt(i++));h4=b64.indexOf(data.charAt(i++));bits=h1<<18|h2<<12|h3<<6|h4;o1=bits>>16&0xff;o2=bits>>8&0xff;o3=bits&0xff;if(h3==64){tmp_arr[ac++]=String.fromCharCode(o1);}else if(h4==64){tmp_arr[ac++]=String.fromCharCode(o1,o2);}else{tmp_arr[ac++]=String.fromCharCode(o1,o2,o3);}}while(i<data.length);dec=tmp_arr.join('');return dec;}
function base64_encode(data){var b64="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var o1,o2,o3,h1,h2,h3,h4,bits,i=0,ac=0,enc="",tmp_arr=[];if(!data){return data;}
do{o1=data.charCodeAt(i++);o2=data.charCodeAt(i++);o3=data.charCodeAt(i++);bits=o1<<16|o2<<8|o3;h1=bits>>18&0x3f;h2=bits>>12&0x3f;h3=bits>>6&0x3f;h4=bits&0x3f;tmp_arr[ac++]=b64.charAt(h1)+b64.charAt(h2)+b64.charAt(h3)+b64.charAt(h4);}while(i<data.length);enc=tmp_arr.join('');var r=data.length%3;return(r?enc.slice(0,r-3):enc)+'==='.slice(r||3);}
function parse_url(str,component){var query,key=['source','scheme','authority','userInfo','user','pass','host','port','relative','path','directory','file','query','fragment'],ini=(this.php_js&&this.php_js.ini)||{},mode=(ini['phpjs.parse_url.mode']&&ini['phpjs.parse_url.mode'].local_value)||'php',parser={php:/^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,strict:/^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,loose:/^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/};var m=parser[mode].exec(str),uri={},i=14;while(i--){if(m[i]){uri[key[i]]=m[i];}}
if(component){return uri[component.replace('PHP_URL_','').toLowerCase()];}
if(mode!=='php'){var name=(ini['phpjs.parse_url.queryKey']&&ini['phpjs.parse_url.queryKey'].local_value)||'queryKey';parser=/(?:^|&)([^&=]*)=?([^&]*)/g;uri[name]={};query=uri[key[12]]||'';query.replace(parser,function($0,$1,$2){if($1){uri[name][$1]=$2;}});}
delete uri.source;return uri;}
function empty(mixed_var){var undef,key,i,len;var emptyValues=[undef,null,false,0,"","0"];for(i=0,len=emptyValues.length;i<len;i++){if(mixed_var===emptyValues[i]){return true;}}
if(typeof mixed_var==="object"){for(key in mixed_var){return false;}
return true;}
return false;}
function is_array(mixed_var){var ini,_getFuncName=function(fn){var name=(/\W*function\s+([\w\$]+)\s*\(/).exec(fn);if(!name){return'(Anonymous)';}
return name[1];},_isArray=function(mixed_var){if(!mixed_var||typeof mixed_var!=='object'||typeof mixed_var.length!=='number'){return false;}
var len=mixed_var.length;mixed_var[mixed_var.length]='bogus';if(len!==mixed_var.length){mixed_var.length-=1;return true;}
delete mixed_var[mixed_var.length];return false;};if(!mixed_var||typeof mixed_var!=='object'){return false;}
this.php_js=this.php_js||{};this.php_js.ini=this.php_js.ini||{};ini=this.php_js.ini['phpjs.objectsAsArrays'];return _isArray(mixed_var)||((!ini||((parseInt(ini.local_value,10)!==0&&(!ini.local_value.toLowerCase||ini.local_value.toLowerCase()!=='off'))))&&(Object.prototype.toString.call(mixed_var)==='[object Object]'&&_getFuncName(mixed_var.constructor)==='Object'));}
function is_numeric(mixed_var){return(typeof mixed_var==='number'||typeof mixed_var==='string')&&mixed_var!==''&&!isNaN(mixed_var);}
function is_string(mixed_var){return(typeof mixed_var==='string');}
function isset(){var a=arguments,l=a.length,i=0,undef;if(l===0){throw new Error('Empty isset');}
while(i!==l){if(a[i]===undef||a[i]===null){return false;}
i++;}
return true;}
function print_r(array,return_val){var output='',pad_char=' ',pad_val=4,d=this.window.document,getFuncName=function(fn){var name=(/\W*function\s+([\w\$]+)\s*\(/).exec(fn);if(!name){return'(Anonymous)';}
return name[1];},repeat_char=function(len,pad_char){var str='';for(var i=0;i<len;i++){str+=pad_char;}
return str;},formatArray=function(obj,cur_depth,pad_val,pad_char){if(cur_depth>0){cur_depth++;}
var base_pad=repeat_char(pad_val*cur_depth,pad_char);var thick_pad=repeat_char(pad_val*(cur_depth+1),pad_char);var str='';if(typeof obj==='object'&&obj!==null&&obj.constructor&&getFuncName(obj.constructor)!=='PHPJS_Resource'){str+='Array\n'+base_pad+'(\n';for(var key in obj){if(Object.prototype.toString.call(obj[key])==='[object Array]'){str+=thick_pad+'['+key+'] => '+formatArray(obj[key],cur_depth+1,pad_val,pad_char);}
else{str+=thick_pad+'['+key+'] => '+obj[key]+'\n';}}
str+=base_pad+')\n';}
else if(obj===null||obj===undefined){str='';}
else{str=obj.toString();}
return str;};output=formatArray(array,0,pad_val,pad_char);if(return_val!==true){if(d.body){this.echo(output);}
else{try{d=XULDocument;this.echo('<pre xmlns="http://www.w3.org/1999/xhtml" style="white-space:pre;">'+output+'</pre>');}catch(e){this.echo(output);}}
return true;}
return output;}
function serialize(mixed_value){var val,key,okey,ktype='',vals='',count=0,_utf8Size=function(str){var size=0,i=0,l=str.length,code='';for(i=0;i<l;i++){code=str.charCodeAt(i);if(code<0x0080){size+=1;}
else if(code<0x0800){size+=2;}
else{size+=3;}}
return size;},_getType=function(inp){var match,key,cons,types,type=typeof inp;if(type==='object'&&!inp){return'null';}
if(type==='object'){if(!inp.constructor){return'object';}
cons=inp.constructor.toString();match=cons.match(/(\w+)\(/);if(match){cons=match[1].toLowerCase();}
types=['boolean','number','string','array'];for(key in types){if(cons==types[key]){type=types[key];break;}}}
return type;},type=_getType(mixed_value);switch(type){case'function':val='';break;case'boolean':val='b:'+(mixed_value?'1':'0');break;case'number':val=(Math.round(mixed_value)==mixed_value?'i':'d')+':'+mixed_value;break;case'string':val='s:'+_utf8Size(mixed_value)+':"'+mixed_value+'"';break;case'array':case'object':val='a';for(key in mixed_value){if(mixed_value.hasOwnProperty(key)){ktype=_getType(mixed_value[key]);if(ktype==='function'){continue;}
okey=(key.match(/^[0-9]+$/)?parseInt(key,10):key);vals+=this.serialize(okey)+this.serialize(mixed_value[key]);count++;}}
val+=':'+count+':{'+vals+'}';break;case'undefined':default:val='N';break;}
if(type!=='object'&&type!=='array'){val+=';';}
return val;}
function unserialize(data){var that=this,utf8Overhead=function(chr){var code=chr.charCodeAt(0);if(code<0x0080){return 0;}
if(code<0x0800){return 1;}
return 2;},error=function(type,msg,filename,line){throw new that.window[type](msg,filename,line);},read_until=function(data,offset,stopchr){var i=2,buf=[],chr=data.slice(offset,offset+1);while(chr!=stopchr){if((i+offset)>data.length){error('Error','Invalid');}
buf.push(chr);chr=data.slice(offset+(i-1),offset+i);i+=1;}
return[buf.length,buf.join('')];},read_chrs=function(data,offset,length){var i,chr,buf;buf=[];for(i=0;i<length;i++){chr=data.slice(offset+(i-1),offset+i);buf.push(chr);length-=utf8Overhead(chr);}
return[buf.length,buf.join('')];},_unserialize=function(data,offset){var dtype,dataoffset,keyandchrs,keys,readdata,readData,ccount,stringlength,i,key,kprops,kchrs,vprops,vchrs,value,chrs=0,typeconvert=function(x){return x;};if(!offset){offset=0;}
dtype=(data.slice(offset,offset+1)).toLowerCase();dataoffset=offset+2;switch(dtype){case'i':typeconvert=function(x){return parseInt(x,10);};readData=read_until(data,dataoffset,';');chrs=readData[0];readdata=readData[1];dataoffset+=chrs+1;break;case'b':typeconvert=function(x){return parseInt(x,10)!==0;};readData=read_until(data,dataoffset,';');chrs=readData[0];readdata=readData[1];dataoffset+=chrs+1;break;case'd':typeconvert=function(x){return parseFloat(x);};readData=read_until(data,dataoffset,';');chrs=readData[0];readdata=readData[1];dataoffset+=chrs+1;break;case'n':readdata=null;break;case's':ccount=read_until(data,dataoffset,':');chrs=ccount[0];stringlength=ccount[1];dataoffset+=chrs+2;readData=read_chrs(data,dataoffset+1,parseInt(stringlength,10));chrs=readData[0];readdata=readData[1];dataoffset+=chrs+2;if(chrs!=parseInt(stringlength,10)&&chrs!=readdata.length){error('SyntaxError','String length mismatch');}
break;case'a':readdata={};keyandchrs=read_until(data,dataoffset,':');chrs=keyandchrs[0];keys=keyandchrs[1];dataoffset+=chrs+2;for(i=0;i<parseInt(keys,10);i++){kprops=_unserialize(data,dataoffset);kchrs=kprops[1];key=kprops[2];dataoffset+=kchrs;vprops=_unserialize(data,dataoffset);vchrs=vprops[1];value=vprops[2];dataoffset+=vchrs;readdata[key]=value;}
dataoffset+=1;break;default:error('SyntaxError','Unknown / Unhandled data type(s): '+dtype);break;}
return[dtype,dataoffset-offset,typeconvert(readdata)];};return _unserialize((data+''),0)[2];}
function var_dump(){var output='',pad_char=' ',pad_val=4,lgth=0,i=0,d=this.window.document;var _getFuncName=function(fn){var name=(/\W*function\s+([\w\$]+)\s*\(/).exec(fn);if(!name){return'(Anonymous)';}
return name[1];};var _repeat_char=function(len,pad_char){var str='';for(var i=0;i<len;i++){str+=pad_char;}
return str;};var _getInnerVal=function(val,thick_pad){var ret='';if(val===null){ret='NULL';}else if(typeof val==='boolean'){ret='bool('+val+')';}else if(typeof val==='string'){ret='string('+val.length+') "'+val+'"';}else if(typeof val==='number'){if(parseFloat(val)==parseInt(val,10)){ret='int('+val+')';}else{ret='float('+val+')';}}
else if(typeof val==='undefined'){ret='undefined';}else if(typeof val==='function'){var funcLines=val.toString().split('\n');ret='';for(var i=0,fll=funcLines.length;i<fll;i++){ret+=(i!==0?'\n'+thick_pad:'')+funcLines[i];}}else if(val instanceof Date){ret='Date('+val+')';}else if(val instanceof RegExp){ret='RegExp('+val+')';}else if(val.nodeName){switch(val.nodeType){case 1:if(typeof val.namespaceURI==='undefined'||val.namespaceURI==='http://www.w3.org/1999/xhtml'){ret='HTMLElement("'+val.nodeName+'")';}else{ret='XML Element("'+val.nodeName+'")';}
break;case 2:ret='ATTRIBUTE_NODE('+val.nodeName+')';break;case 3:ret='TEXT_NODE('+val.nodeValue+')';break;case 4:ret='CDATA_SECTION_NODE('+val.nodeValue+')';break;case 5:ret='ENTITY_REFERENCE_NODE';break;case 6:ret='ENTITY_NODE';break;case 7:ret='PROCESSING_INSTRUCTION_NODE('+val.nodeName+':'+val.nodeValue+')';break;case 8:ret='COMMENT_NODE('+val.nodeValue+')';break;case 9:ret='DOCUMENT_NODE';break;case 10:ret='DOCUMENT_TYPE_NODE';break;case 11:ret='DOCUMENT_FRAGMENT_NODE';break;case 12:ret='NOTATION_NODE';break;}}
return ret;};var _formatArray=function(obj,cur_depth,pad_val,pad_char){var someProp='';if(cur_depth>0){cur_depth++;}
var base_pad=_repeat_char(pad_val*(cur_depth-1),pad_char);var thick_pad=_repeat_char(pad_val*(cur_depth+1),pad_char);var str='';var val='';if(typeof obj==='object'&&obj!==null){if(obj.constructor&&_getFuncName(obj.constructor)==='PHPJS_Resource'){return obj.var_dump();}
lgth=0;for(someProp in obj){lgth++;}
str+='array('+lgth+') {\n';for(var key in obj){var objVal=obj[key];if(typeof objVal==='object'&&objVal!==null&&!(objVal instanceof Date)&&!(objVal instanceof RegExp)&&!objVal.nodeName){str+=thick_pad+'['+key+'] =>\n'+thick_pad+_formatArray(objVal,cur_depth+1,pad_val,pad_char);}else{val=_getInnerVal(objVal,thick_pad);str+=thick_pad+'['+key+'] =>\n'+thick_pad+val+'\n';}}
str+=base_pad+'}\n';}else{str=_getInnerVal(obj,thick_pad);}
return str;};output=_formatArray(arguments[0],0,pad_val,pad_char);for(i=1;i<arguments.length;i++){output+='\n'+_formatArray(arguments[i],0,pad_val,pad_char);}
if(d.body){this.echo(output);}else{try{d=XULDocument;this.echo('<pre xmlns="http://www.w3.org/1999/xhtml" style="white-space:pre;">'+output+'</pre>');}catch(e){this.echo(output);}}}
function var_export(mixed_expression,bool_return){var retstr='',iret='',value,cnt=0,x=[],i=0,funcParts=[],idtLevel=arguments[2]||2,innerIndent='',outerIndent='',getFuncName=function(fn){var name=(/\W*function\s+([\w\$]+)\s*\(/).exec(fn);if(!name){return'(Anonymous)';}
return name[1];},_makeIndent=function(idtLevel){return(new Array(idtLevel+1)).join(' ');},__getType=function(inp){var i=0,match,types,cons,type=typeof inp;if(type==='object'&&inp.constructor&&getFuncName(inp.constructor)==='PHPJS_Resource'){return'resource';}
if(type==='function'){return'function';}
if(type==='object'&&!inp){return'null';}
if(type==="object"){if(!inp.constructor){return'object';}
cons=inp.constructor.toString();match=cons.match(/(\w+)\(/);if(match){cons=match[1].toLowerCase();}
types=["boolean","number","string","array"];for(i=0;i<types.length;i++){if(cons===types[i]){type=types[i];break;}}}
return type;},type=__getType(mixed_expression);if(type===null){retstr="NULL";}else if(type==='array'||type==='object'){outerIndent=_makeIndent(idtLevel-2);innerIndent=_makeIndent(idtLevel);for(i in mixed_expression){value=this.var_export(mixed_expression[i],1,idtLevel+2);value=typeof value==='string'?value.replace(/</g,'&lt;').replace(/>/g,'&gt;'):value;x[cnt++]=innerIndent+i+' => '+
(__getType(mixed_expression[i])==='array'?'\n':'')+value;}
iret=x.join(',\n');retstr=outerIndent+"array (\n"+iret+'\n'+outerIndent+')';}else if(type==='function'){funcParts=mixed_expression.toString().match(/function .*?\((.*?)\) \{([\s\S]*)\}/);retstr="create_function ('"+funcParts[1]+"', '"+
funcParts[2].replace(new RegExp("'",'g'),"\\'")+"')";}else if(type==='resource'){retstr='NULL';}else{retstr=typeof mixed_expression!=='string'?mixed_expression:"'"+mixed_expression.replace(/(["'])/g,"\\$1").replace(/\0/g,"\\0")+"'";}
if(!bool_return){this.echo(retstr);return null;}
return retstr;}
function utf8_encode(argString){if(argString===null||typeof argString==="undefined"){return"";}
var string=(argString+'');var utftext='',start,end,stringl=0;start=end=0;stringl=string.length;for(var n=0;n<stringl;n++){var c1=string.charCodeAt(n);var enc=null;if(c1<128){end++;}else if(c1>127&&c1<2048){enc=String.fromCharCode((c1>>6)|192,(c1&63)|128);}else if(c1&0xF800!=0xD800){enc=String.fromCharCode((c1>>12)|224,((c1>>6)&63)|128,(c1&63)|128);}else{if(c1&0xFC00!=0xD800){throw new RangeError("Unmatched trail surrogate at "+n);}
var c2=string.charCodeAt(++n);if(c2&0xFC00!=0xDC00){throw new RangeError("Unmatched lead surrogate at "+(n-1));}
c1=((c1&0x3FF)<<10)+(c2&0x3FF)+0x10000;enc=String.fromCharCode((c1>>18)|240,((c1>>12)&63)|128,((c1>>6)&63)|128,(c1&63)|128);}
if(enc!==null){if(end>start){utftext+=string.slice(start,end);}
utftext+=enc;start=end=n+1;}}
if(end>start){utftext+=string.slice(start,stringl);}
return utftext;}
function trigger_error(error_msg,error_type){var type=0,i=0,that=this,prepend='',append='';if(!error_type){error_type='E_USER_NOTICE';}
var ini_on=function(ini){return that.php_js.ini[ini]&&that.php_js.ini[ini].local_value&&((that.php_js.ini[ini].local_value.toString&&that.php_js.ini[ini].local_value.toString().toLowerCase&&(that.php_js.ini[ini].local_value.toString().toLowerCase()==='on'||that.php_js.ini[ini].local_value.toString().toLowerCase()==='true'))||parseInt(that.php_js.ini[ini].local_value,10)===1);};var display_errors=function(type){return that.php_js.ini.error_reporting&&(type&that.php_js.ini.error_reporting.local_value)&&ini_on('display_errors');};var TYPES={E_ERROR:1,E_WARNING:2,E_PARSE:4,E_NOTICE:8,E_CORE_ERROR:16,E_CORE_WARNING:32,E_COMPILE_ERROR:64,E_COMPILE_WARNING:128,E_USER_ERROR:256,E_USER_WARNING:512,E_USER_NOTICE:1024,E_STRICT:2048,E_RECOVERABLE_ERROR:4096,E_DEPRECATED:8192,E_USER_DEPRECATED:16384,E_ALL:30719};if(typeof error_type==='number'){type=error_type;}else{error_type=[].concat(error_type);for(i=0;i<error_type.length;i++){if(TYPES[error_type[i]]){type=type|TYPES[error_type[i]];}}}
this.php_js=this.php_js||{};this.php_js.ini=this.php_js.ini||{};if(type&TYPES.E_USER_ERROR||type&TYPES.E_ERROR||type&TYPES.E_CORE_ERROR||type&TYPES.E_COMPILE_ERROR||type&TYPES.E_RECOVERABLE_ERROR||type&TYPES.E_PARSE){if(ini_on('track_errors')){this.$php_errormsg=error_msg;}
if(display_errors(type)){prepend=this.php_js.ini.error_prepend_string?this.php_js.ini.error_prepend_string:'';append=this.php_js.ini.error_append_string?this.php_js.ini.error_append_string:'';this.echo(prepend+'Error: '+error_msg+' '+append);}
var e=new Error(error_msg);e.type=type;this.php_js.last_error={message:e.message,file:e.fileName,line:e.lineNumber,type:e.type};throw e;}
if(display_errors(type)){switch(type){case TYPES.E_USER_WARNING:case TYPES.E_WARNING:case TYPES.E_CORE_WARNING:case TYPES.E_COMPILE_WARNING:this.echo('Warning: '+error_msg);break;case TYPES.E_USER_NOTICE:case TYPES.E_NOTICE:this.echo('Notice: '+error_msg);break;case TYPES.E_DEPRECATED:case TYPES.E_USER_DEPRECATED:this.echo('Deprecated: '+error_msg);break;default:throw'Unrecognized error type';}}
return true;}