<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Sales 2016</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f5f5f5; padding: 40px 0;">
             
                <!-- Demo Graph Section -->
                <div class="card shadow-sm mt-4" style="border: none; border-radius: 8px;">
                    <div class="card-body" style="padding: 40px;">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
                            <h5 style="margin: 0; font-weight: 300;">📊 DEMO ONLY - Interactive Preview</h5>
                        </div>
                        
                        <!-- Chart Title -->
                        <h2 style="text-align: center; color: #9b9b9b; font-weight: 300; margin-bottom: 40px; font-size: 2rem;">Monthly Trends 2016</h2>
                        
                        <!-- Chart Container -->
                        <div style="position: relative; height: 400px; border-left: 2px solid #e0e0e0; border-bottom: 2px solid #e0e0e0; padding: 20px 20px 40px 60px;">
                            <!-- Y-axis labels -->
                            <div style="position: absolute; left: 0; top: 20px; bottom: 40px; width: 60px; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end; padding-right: 10px;">
                                <span style="color: #9b9b9b; font-size: 14px;">100K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">90K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">80K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">70K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">60K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">50K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">40K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">30K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">20K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">10K</span>
                                <span style="color: #9b9b9b; font-size: 14px;">0</span>
                            </div>
                            
                            <!-- Grid lines -->
                            <div style="position: absolute; left: 60px; right: 20px; top: 20px; bottom: 40px;">
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 0%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 10%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 20%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 30%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 40%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 50%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 60%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 70%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 80%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 90%;"></div>
                                <div style="position: absolute; width: 100%; height: 1px; background-color: #f0f0f0; top: 100%;"></div>
                            </div>
                            
                            <!-- SVG Line Graph -->
                            <svg style="position: absolute; left: 60px; right: 20px; top: 20px; bottom: 40px; width: calc(100% - 80px); height: calc(100% - 60px);" viewBox="0 0 1000 340">
                                <!-- Gradient for area fill -->
                                <defs>
                                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:0.4" />
                                        <stop offset="100%" style="stop-color:#667eea;stop-opacity:0.05" />
                                    </linearGradient>
                                    <filter id="shadow">
                                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.3"/>
                                    </filter>
                                </defs>
                                
                                <!-- Area under the line -->
                                <path d="M 0,170 L 91,142 L 182,139 L 273,115 L 364,131 L 455,124 L 546,134 L 637,130 L 728,123 L 819,119 L 910,117 L 1000,110 L 1000,340 L 0,340 Z" 
                                      fill="url(#lineGradient)" />
                                
                                <!-- Main line -->
                                <path d="M 0,170 L 91,142 L 182,139 L 273,115 L 364,131 L 455,124 L 546,134 L 637,130 L 728,123 L 819,119 L 910,117 L 1000,110" 
                                      stroke="#667eea" 
                                      stroke-width="3" 
                                      fill="none" 
                                      filter="url(#shadow)"
                                      style="transition: all 0.3s;">
                                    <animate attributeName="stroke-dasharray" from="0,2000" to="2000,0" dur="2s" />
                                </path>
                                
                                <!-- Data points -->
                                <!-- Jan - 50K -->
                                <circle cx="0" cy="170" r="6" fill="#fff" stroke="#667eea" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="0.2s" fill="freeze" />
                                </circle>
                                
                                <!-- Feb - 58K -->
                                <circle cx="91" cy="142" r="6" fill="#fff" stroke="#667eea" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="0.4s" fill="freeze" />
                                </circle>
                                
                                <!-- Mar - 61K -->
                                <circle cx="182" cy="139" r="6" fill="#fff" stroke="#667eea" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="0.6s" fill="freeze" />
                                </circle>
                                
                                <!-- Apr - 75K -->
                                <circle cx="273" cy="115" r="6" fill="#fff" stroke="#48bb78" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="0.8s" fill="freeze" />
                                </circle>
                                
                                <!-- May - 69K -->
                                <circle cx="364" cy="131" r="6" fill="#fff" stroke="#667eea" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="1s" fill="freeze" />
                                </circle>
                                
                                <!-- Jun - 76K -->
                                <circle cx="455" cy="124" r="6" fill="#fff" stroke="#48bb78" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="1.2s" fill="freeze" />
                                </circle>
                                
                                <!-- Jul - 66K -->
                                <circle cx="546" cy="134" r="6" fill="#fff" stroke="#667eea" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="1.4s" fill="freeze" />
                                </circle>
                                
                                <!-- Aug - 70K -->
                                <circle cx="637" cy="130" r="6" fill="#fff" stroke="#667eea" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="1.6s" fill="freeze" />
                                </circle>
                                
                                <!-- Sep - 77K -->
                                <circle cx="728" cy="123" r="6" fill="#fff" stroke="#f59e0b" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="1.8s" fill="freeze" />
                                </circle>
                                
                                <!-- Oct - 81K -->
                                <circle cx="819" cy="119" r="6" fill="#fff" stroke="#f59e0b" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="2s" fill="freeze" />
                                </circle>
                                
                                <!-- Nov - 83K -->
                                <circle cx="910" cy="117" r="6" fill="#fff" stroke="#e74c3c" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="2.2s" fill="freeze" />
                                </circle>
                                
                                <!-- Dec - 90K -->
                                <circle cx="1000" cy="110" r="6" fill="#fff" stroke="#e74c3c" stroke-width="3" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.setAttribute('r', '9')" onmouseout="this.setAttribute('r', '6')">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="2.4s" fill="freeze" />
                                </circle>
                            </svg>
                            
                            <!-- X-axis labels -->
                            <div style="position: absolute; left: 60px; right: 20px; bottom: 0; height: 40px; display: flex; justify-content: space-between; gap: 8px;">
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Jan</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Feb</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Mar</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Apr</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">May</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Jun</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Jul</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Aug</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Sep</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Oct</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Nov</div>
                                <div style="flex: 1; text-align: center; color: #9b9b9b; font-size: 13px; padding-top: 10px;">Dec</div>
                            </div>
                            
                        
                        </div>
                        
                        <!-- Stats Cards -->
                        <div class="row mt-4">
                            <div class="col-md-3 col-6 mb-3">
                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 24px; font-weight: bold;">856K</div>
                                    <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">Total Sales</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); padding: 20px; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 24px; font-weight: bold;">+50%</div>
                                    <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">Growth Rate</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 20px; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 24px; font-weight: bold;">254K</div>
                                    <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">Best Quarter</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); padding: 20px; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 24px; font-weight: bold;">71K</div>
                                    <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">Avg/Month</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>