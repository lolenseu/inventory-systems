@extends('layout')
@section('title', 'RegearMe')

@section('content')

<div class="form-container">
    <h2>ReGearMe</h2>

    <div class="info-row">
        <p>
            Learn how your guild’s ReGearMe system works.
        </p>

        <!-- Info button next to p tag -->
        <div class="info-button" onclick="openRegearInfo()">
            <i class="fa fa-info">i</i>
        </div>
    </div>
</div>

<!-- Popup Modal -->
<div id="regearModal" class="regear-modal">
    <div class="regear-modal-content">
        <span class="close" onclick="closeRegearInfo()">&times;</span>

        <h3>What is ReGearMe?</h3>
        <p>
            RegearMe is the Albion Online regear system.  
            When a guild member dies in ZvZ(PvP), they may request regear
            based on guild rules.  
            This feature helps track deaths, verify builds,  
            and organize item replacements efficiently.
        </p>

        <hr style="margin: 20px 0;">

        <h4 style="color: #ff4444;">🚧 Under Development</h4>
        <p>
            Contact me on Discord for updates:<br>
            <a href="https://discordapp.com/users/1062044543874240633" 
               style="color:#0078d7; font-weight:bold;" target="_blank">
                lolenseu
            </a>
        </p>
    </div>
</div>

<style>
/* Transparent container */
.form-container {
    width: 100%;
    max-width: 500px;
    margin: 140px auto; /* dead center horizontally */
    padding: 30px;
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
    border-radius: 12px;
    box-sizing: border-box;
}

/* Text styling */
.form-container h2 {
    color: white;
    font-size: 38px;
    margin-bottom: 20px;
}

/* Row with text + info button */
.info-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px; /* space between text and button */
    flex-wrap: wrap;
}

.form-container p {
    color: white;
    font-size: 18px;
    margin: 0;
}

/* Info button styling */
.info-button {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: transform 0.2s, background 0.2s;
}

.info-button i {
    color: white;
    font-weight: bold;
    font-size: 16px;
}

.info-button:hover {
    transform: scale(1.2);
    background: rgba(255,255,255,0.6);
}

/* Modal styling */
.regear-modal {
    display: none; 
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px;
    box-sizing: border-box;
}

.regear-modal-content {
    background: white;
    width: 100%;
    max-width: 450px;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    animation: pop 0.2s ease-out;
    box-sizing: border-box;
}

/* Animation */
@keyframes pop {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* Close button */
.regear-modal .close {
    float: right;
    font-size: 28px;
    cursor: pointer;
    color: #444;
}

/* Responsive */
@media (max-width: 768px) {
    .form-container { padding: 20px; margin: 80px auto; }
    .form-container h2 { font-size: 36px; }
    .form-container p { font-size: 18px; }
    .info-button { width: 40px; height: 40px; }
    .info-button i { font-size: 20px; }

    .regear-modal-content {
        max-width: calc(100vw - 30px);
    }
}

@media (max-width: 480px) {
    .form-container { padding: 15px; margin: 60px auto; }
    .form-container h2 { font-size: 28px; }
    .form-container p { font-size: 16px; }
    .info-button { width: 35px; height: 35px; }
    .info-button i { font-size: 16px; }

    .regear-modal-content { max-width: calc(100vw - 20px); padding: 20px; }
}

@media (max-width: 360px) {
    .form-container { padding: 12px; margin: 50px auto; max-width: calc(100vw - 15px); }
    .form-container h2 { font-size: 24px; }
    .form-container p { font-size: 14px; }
    .info-button { width: 24px; height: 24px; }
    .info-button i { font-size: 14px; }

    .regear-modal-content { max-width: calc(100vw - 15px); padding: 15px; }
}
</style>

<script>
function openRegearInfo() {
    document.getElementById('regearModal').style.display = 'flex';
}
function closeRegearInfo() {
    document.getElementById('regearModal').style.display = 'none';
}
</script>

@endsection
