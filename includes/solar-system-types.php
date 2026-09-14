<?php
$solarSystemTypes = [
    [
        'id' => 'gridtied', 'name' => 'Grid-Tied',
        'description' => 'Make the most of daytime sunshine with a solar setup connected to the utility grid.',
        'grid' => 'Connected', 'battery' => 'Not required', 'best' => 'Homes & businesses with daytime energy use',
        'features' => ['Solar powers your daytime energy needs', 'The grid supplies power when solar falls short', 'Export excess energy with approved net metering'],
        'note' => 'Shuts down during a grid outage.',
    ],
    [
        'id' => 'hybrid', 'name' => 'Hybrid',
        'description' => 'Combine solar, battery storage, and grid power for a more flexible energy setup.',
        'grid' => 'Connected', 'battery' => 'Included in setup', 'best' => 'Properties that need backup for essential loads',
        'features' => ['Use solar energy and store excess in a battery', 'Draw on stored power when you need it', 'Keep the grid as an additional power source'],
        'note' => 'Backup depends on battery capacity and system design.',
    ],
    [
        'id' => 'offgrid', 'name' => 'Off-Grid',
        'description' => 'Generate and store your own power for places beyond the reach of the utility grid.',
        'grid' => 'Not required', 'battery' => 'Required', 'best' => 'Remote homes, farms & properties without grid access',
        'features' => ['Operate independently of the utility grid', 'Store solar energy for use after sunset', 'Size panels and storage around your daily needs'],
        'note' => 'Extended cloudy periods may require backup generation.',
    ],
];
?>
<section class="solar-options" aria-labelledby="solar-options-title" data-checkout-hide>
    <div class="container">
        <div class="solar-options-heading">
            <div>
                <h2 id="solar-options-title">Types of Solar <span>Systems</span></h2>
            </div>
            <p>Different spaces. Different energy needs.<br>Explore the setup that works for your home or business.</p>
        </div>
        <div class="solar-options-progression"><span>Grid-connected</span><span>More self-sufficient</span></div>
        <div class="solar-options-grid">
            <?php foreach ($solarSystemTypes as $system): ?>
                <article class="solar-option-card solar-option-card--<?= $system['id'] ?>" id="system-<?= $system['id'] ?>" aria-labelledby="solar-option-<?= $system['id'] ?>">
                    <div class="solar-option-connection"><?= $system['id'] === 'offgrid' ? 'Independent of the utility grid' : ($system['id'] === 'hybrid' ? 'Grid connection + stored energy' : 'Direct utility-grid connection') ?></div>
                    <h3 id="solar-option-<?= $system['id'] ?>"><?= $system['name'] ?><span>Solar System</span></h3>
                    <svg class="solar-option-diagram" viewBox="0 0 320 200" role="img" aria-labelledby="diagram-<?= $system['id'] ?>">
                        <title id="diagram-<?= $system['id'] ?>"><?= $system['id'] === 'gridtied' ? 'Solar and utility grid supply the property. No battery storage.' : ($system['id'] === 'hybrid' ? 'Solar, utility grid, and battery storage connect to the property.' : 'Solar and battery storage supply the property without a utility-grid connection.') ?> Simplified energy connections.</title>
                        <path class="solar-wire" d="M65 75 H160 V92"/>
                        <?php if ($system['id'] !== 'offgrid'): ?>
                            <path class="grid-wire" d="M258 66 V75 H160"/>
                            <path class="grid-node" d="M246 42 H270 M250 50 H266 M258 38 V66 M254 42 L248 66 M262 42 L268 66"/>
                            <text x="258" y="26">Utility grid</text>
                        <?php else: ?>
                            <text class="disconnected-label" x="258" y="52">No grid</text>
                            <path class="disconnected-mark" d="M249 67 H257 M264 67 H272 M257 70 L264 62"/>
                        <?php endif; ?>
                        <text x="65" y="26">Solar panels</text>
                        <path class="panel-node" d="M44 40 H86 L91 64 H39 Z M43 52 H88 M58 40 L56 64 M72 40 L74 64 M65 64 V75"/>
                        <path class="property-node" d="M132 114 L160 91 L188 114 M138 109 V139 H182 V109 M155 139 V124 H165 V139"/>
                        <text x="160" y="157">Home / business</text>
                        <?php if ($system['id'] !== 'gridtied'): ?>
                            <path class="storage-wire" d="M182 126 H258 V140"/>
                            <rect class="battery-node" x="248" y="143" width="20" height="30" rx="1"/>
                            <path class="storage-wire" d="M254 139 H262 M252 153 H264 M252 161 H264"/>
                            <text x="258" y="192">Battery storage</text>
                        <?php else: ?>
                            <text class="disconnected-label" x="258" y="164">No battery</text>
                        <?php endif; ?>
                    </svg>
                    <div class="solar-option-body">
                        <p class="solar-option-description"><?= $system['description'] ?></p>
                        <dl class="solar-option-specs">
                            <div><dt>Utility grid</dt><dd><?= $system['grid'] ?></dd></div>
                            <div><dt>Battery storage</dt><dd><?= $system['battery'] ?></dd></div>
                        </dl>
                        <div class="solar-option-best"><span>Ideal for</span><p><?= $system['best'] ?></p></div>
                        <ul class="solar-option-features">
                            <?php foreach ($system['features'] as $feature): ?>
                                <li><?= $feature ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="solar-option-note"><strong>Keep in mind</strong><span><?= $system['note'] ?></span></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="solar-options-help">
            <div><h3>Not sure which system fits?</h3><p>Let’s look at your energy use, property, and backup needs together.</p></div>
            <button type="button" class="solar-options-cta" data-bs-toggle="modal" data-bs-target="#inspectionModal">Get a free assessment</button>
        </div>
    </div>
</section>
