<?php
session_start();
if(isset($_SESSION['email'])){
$idd = $_SESSION['id'];
$id = $_GET['id'];
include('dbcon.php');
$sql = "SELECT * FROM user WHERE id = '$id'";
$exe = mysqli_query($con,$sql);
$row = mysqli_fetch_assoc($exe);
require('fpdf182/fpdf.php');
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',22);
$pdf->Cell(190,20,'Download '.$row['s_name'].'`s Information',1,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->SetFont('Arial','B',22);
$pdf->Cell(190,20, $row['email'],1,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(0,10,'',0,2);
$pdf->cell(20,8,'Name',1,0,'C');
$pdf->SetFont('Arial','B',14);
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(150,8,$row['s_name'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(40,8,'Father`s Name',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(150,8,$row['f_name'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(40,8,'Mother`s Name',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(150,8,$row['m_name'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(40,8,'Date Of Birth',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(150,8,$row['dod'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(40,8,'Contact',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(150,8,$row['phone'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(70,8,'Permanent Address Care Of',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(120,8,$row['per_careof'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(50,8,'Permanent Village',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(120,8,$row['per_village'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->SetFont('Arial','',14);
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(50,8,'Permanent Division',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(120,8,$row['pdivi'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(50,8,'Permanent District',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(120,8,$row['pdist'],1,1,'C');
$pdf->cell(0,6,'',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->cell(50,8,'Permanent PS/Thana',1,0,'C');
$pdf->cell(2,0,'',0,0,'C');
$pdf->cell(120,8,$row['p_posto'],1,1,'C');

$pdf->Image("../../user_img/".$row['image'],10,220,70,70);;

$pdf->Image("../../user_img/nid/".$row['nid'],80,220,120,70);;

$pdf->Output();

}else{
    header('location: ../../login.php');
}