<?php
/**
* =====================================================================================
* Class for base module for Popbill API SDK. It include base functionality for
* RESTful web service request and parse json result. It uses Linkhub module
* to accomplish authentication APIs.
*
* This module uses curl and openssl for HTTPS Request. So related modules must
* be installed and enabled.
*
* http://www.linkhub.co.kr
* Author : Kim Seongjun (pallet027@gmail.com)
* Written : 2014-04-15
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anything.
* ======================================================================================
*/
require_once 'Popbill/popbill.php';

define('MgtKeyType_SELL','SELL');
define('MgtKeyType_BUY','BUY');
define('MgtKeyType_TRUSTEE','TRUSTEE');

class TaxinvoiceService extends PopbillBase {
	
	function TaxinvoiceService($LinkID,$SecretKey) {
    	parent::PopbillBase($LinkID,$SecretKey);
    	$this->AddScope('110');
    }
    
    //팝빌 세금계산서 연결 url
    function GetURL($CorpNum,$UserID,$TOGO) {
    	$result = $this->executeCURL('/Taxinvoice/?TG='.$TOGO,$CorpNum,$UserID);
    	if(is_a($result,'PopbillException')) return $result;
    	
    	return $result->url;
    }
    
    //관리번호 사용여부 확인
    function CheckMgtKeyInUse($CorpNum,$MgtKeyType,$MgtKey) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$response = $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey,$CorpNum);
    	
    	if(is_a($response,'PopbillException')) {
    		if($response->code == -11000005) { return false;}
    		return $response;
    	}
    	else {
    		return is_null($response->itemKey) == false;
    	}
    }
    
    //임시저장
    function Register($CorpNum, $Taxinvoice, $UserID = null, $writeSpecification = false) {
    	if($writeSpecification) {
    		$Taxinvoice->writeSpecification = $writeSpecification;
    	}
    	$postdata = $this->Linkhub->json_encode($Taxinvoice);
    	return $this->executeCURL('/Taxinvoice',$CorpNum,$UserID,true,null,$postdata);
    }    
    
    //삭제
    function Delete($CorpNum,$MgtKeyType,$MgtKey,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'DELETE','');
    }
    
    //수정
    function Update($CorpNum,$MgtKeyType,$MgtKey,$Taxinvoice, $UserID = null, $writeSpecification = false) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	if($writeSpecification) {
    		$Taxinvoice->writeSpecification = $writeSpecification;
    	}
    	
    	$postdata = $this->Linkhub->json_encode($Taxinvoice);
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true, 'PATCH', $postdata);
    }
    
    //발행예정
    function Send($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'SEND',$postdata);
    }
    
    //발행예정취소
    function CancelSend($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'CANCELSEND',$postdata);
    }
    
    //발행예정 승인
    function Accept($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'ACCEPT',$postdata);
    }
    
    //발행예정 거부
    function Deny($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'DENY',$postdata);
    }
    
    //발행
    function Issue($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$EmailSubject = null , $ForceIssue = false, $UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new IssueRequest();
    	$Request->memo = $Memo;
    	$Request->emailSubject = $EmailSubject;
    	$Request->forceIssue = $ForceIssue;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'ISSUE',$postdata);
    }
    
    //발행취소
    function CancelIssue($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'CANCELISSUE',$postdata);
    }
    
    //역)발행요청
    function Request($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'REQUEST',$postdata);
    }
    
    //역)발행요청 거부
    function Refuse($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'REFUSE',$postdata);
    }
    
    //역)발행요청 취소
    function CancelRequest($CorpNum,$MgtKeyType,$MgtKey,$Memo = '',$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	$Request = new MemoRequest();
    	$Request->memo = $Memo;
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'CANCELREQUEST',$postdata);
    }
    
    //국세청 즉시전송 요청
    function SendToNTS($CorpNum,$MgtKeyType,$MgtKey,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'NTS','');
    }
    
    //알림메일 재전송
    function SendEmail($CorpNum,$MgtKeyType,$MgtKey,$Receiver,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$Request = array('receiver' => $Receiver);
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'EMAIL',$postdata);
    }
    
    //알림문자 재전송
    function SendSMS($CorpNum,$MgtKeyType,$MgtKey,$Sender,$Receiver,$Contents,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$Request = array('receiver' => $Receiver,'sender'=>$Sender,'contents' => $Contents);
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'SMS',$postdata);
    }
    
    //알림팩스 재전송
    function SendFAX($CorpNum,$MgtKeyType,$MgtKey,$Sender,$Receiver,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$Request = array('receiver' => $Receiver,'sender'=>$Sender);
    	$postdata = $this->Linkhub->json_encode($Request);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum, $UserID, true,'FAX',$postdata);
    }
    
    //세금계산서 요약정보 및 상태정보 확인
    function GetInfo($CorpNum,$MgtKeyType,$MgtKey) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey, $CorpNum);
    }
    
    //세금계산서 상세정보 확인 
    function GetDetailInfo($CorpNum,$MgtKeyType,$MgtKey) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'?Detail', $CorpNum);
    }
    
    //세금계산서 요약정보 다량확인 최대 1000건
    function GetInfos($CorpNum,$MgtKeyType,$MgtKeyList = array()) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$postdata = $this->Linkhub->json_encode($MgtKeyList);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType, $CorpNum, null, true,null,$postdata);
    }
    
    //세금계산서 문서이력 확인 
    function GetLogs($CorpNum,$MgtKeyType,$MgtKey) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'/Logs', $CorpNum);
    }
    
    //파일첨부
    function AttachFile($CorpNum,$MgtKeyType,$MgtKey,$FilePath , $UserID = null) {
    
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    
    	$postdata = array('Filedata' => '@'.$FilePath);
    	
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'/Files', $CorpNum, $UserID, true,null,$postdata,true);
    
    }
    
    //첨부파일 목록 확인 
    function GetFiles($CorpNum,$MgtKeyType,$MgtKey) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'/Files', $CorpNum);
    }
    
    //첨부파일 삭제 
    function DeleteFile($CorpNum,$MgtKeyType,$MgtKey,$FileID,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	if(is_null($FileID) || empty($FileID)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "파일아이디가 입력되지 않았습니다."}');
    	}
    	return $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'/Files/'.$FileID, $CorpNum,$UserID,true,'DELETE','');
    }
    
    //팝업URL
    function GetPopUpURL($CorpNum,$MgtKeyType,$MgtKey,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$result = $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'?TG=POPUP', $CorpNum,$UserID);
    	if(is_a($result,'PopbillException')) return $result;
    	
    	return $result->url;
    }
    
    //인쇄URL
    function GetPrintURL($CorpNum,$MgtKeyType,$MgtKey,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$result = $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'?TG=PRINT', $CorpNum,$UserID);
    	if(is_a($result,'PopbillException')) return $result;
    	
    	return $result->url;
    }
    
    //공급받는자 메일URL
    function GetMailURL($CorpNum,$MgtKeyType,$MgtKey,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$result = $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'/'.$MgtKey.'?TG=MAIL', $CorpNum,$UserID);
    	if(is_a($result,'PopbillException')) return $result;
    	
    	return $result->url;
    }
    
    //세금계산서 다량인쇄 URL
    function GetMassPrintURL($CorpNum,$MgtKeyType,$MgtKeyList = array(),$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		return new PopbillException('{"code" : -99999999 , "message" : "관리번호가 입력되지 않았습니다."}');
    	}
    	
    	$postdata = $this->Linkhub->json_encode($MgtKeyList);
    	
    	$result = $this->executeCURL('/Taxinvoice/'.$MgtKeyType.'?Print', $CorpNum, $UserID, true,null,$postdata);
    	if(is_a($result,'PopbillException')) return $result;
    	
    	return $result->url;
    }
    
    //회원인증서 만료일 확인
    function GetCertificateExpireDate($CorpNum) {
    	$result = $this->executeCURL('/Taxinvoice?cfg=CERT', $CorpNum);
    	if(is_a($result,'PopbillException')) return $result;
    	
    	return $result->certificateExpiration;
    }
    
    //발행단가 확인
    function GetUnitCost($CorpNum) {
    	$result = $this->executeCURL('/Taxinvoice?cfg=UNITCOST', $CorpNum);
    	if(is_a($result,'PopbillException')) return $result;
    	
    	return $result->unitCost;
    }
    
    //대용량 연계사업자 유통메일목록 확인
    function GetEmailPublicKeys($CorpNum) {
    	return $this->executeCURL('/Taxinvoice/EmailPublicKeys', $CorpNum);
    }
    
}

class Taxinvoice
{
	
	var $writeSpecification;
	var $writeDate;
	var $chargeDirection;
	var $issueType;
	var $issueTiming;
	var $taxType;
	var $ivoicerCorpNum;
	var $invoicerMgtKey;
	var $invoicerTaxRegID;
	var $invoicerCorpName;
	var $invoicerCEOName;
	var $invoicerAddr;
	var $invoicerBizClass;
	var $invoicerBizType;
	var $invoicerContactName;
	var $invoicerDeptName;
	var $invoicerTEL;
	var $invoicerHP;
	var $invoicerEmail;
	var $invoicerSMSSendYN;
	
	var $invoiceeCorpNum;
	var $invoiceeType;
	var $invoiceeMgtKey;
	var $invoiceeTaxRegID;
	var $invoiceeCorpName;
	var $invoiceeCEOName;
	var $invoiceeAddr;
	var $invoiceeBizClass;
	var $invoiceeBizType;
	var $invoiceeContactName1;
	var $invoiceeDeptName1;
	var $invoiceeTEL1;
	var $invoiceeHP1;
	var $invoiceeEmail2;
	var $invoiceeContactName2;
	var $invoiceeDeptName2;
	var $invoiceeTEL2;
	var $invoiceeHP2;
	var $invoiceeEmail1;
	var $invoiceeSMSSendYN;
	
	var $trusteeCorpNum;
	var $trusteeMgtKey;
	var $trusteeTaxRegID;
	var $trusteeCorpName;
	var $trusteeCEOName;
	var $trusteeAddr;
	var $trusteeBizClass;
	var $trusteeBizType;
	var $trusteeContactName;
	var $trusteeDeptName;
	var $trusteeTEL;
	var $trusteeHP;
	var $trusteeEmail;
	var $trusteeSMSSendYN;
	
	var $taxTotal;
	var $supplyCostTotal;
	var $totalAmount;
	var $modifyCode;
	var $purposeType;
	var $serialNum;
	var $cash;
	var $chkBill;
	var $credit;
	var $note;
	var $remark1;
	var $remark2;
	var $remark3;
	var $kwon;
	var $ho;
	var $businessLicenseYN;
	var $bankBookYN;
	var $faxsendYN;
	var $faxreceiveNum;
	var $originalTaxinvoiceKey;
	var $detailList;
	var $addContactList;
	
}
class TaxinvoiceDetail {
	var $serialNum;
	var $purchaseDT;
	var $itemName;
	var $spec;
	var $qty;
	var $unitCost;
	var $supplyCost;
	var $tax;
	var $remark;
}
class TaxinvoiceAddContact {
	var $serialNum;
	var $email;
	var $contactName;
}

class MemoRequest {
	var $memo;
}
class IssueRequest {
	var $memo;
	var $emailSubject;
	var $forceIssue;
}
?>