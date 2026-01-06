<?php
/**
 * Post Choice Page
 * 
 * Let users select what type of listing they want to create.
 */
?>

<section class="post-choice-page">
    <div class="post-choice-container">
        
        <header class="post-choice-header">
            <h1>Post to the Grid</h1>
            <p class="post-choice-subtitle">What would you like to share with the community?</p>
        </header>
        
        <div class="post-choice-grid">
            
            <!-- Need -->
            <div class="post-choice-card <?= $canPostNeeds ? '' : 'post-choice-card-disabled' ?>">
                <div class="post-choice-icon">🟥</div>
                <h2>Post a Need</h2>
                <p>Request resources, supplies, or support for your organization.</p>
                
                <?php if ($canPostNeeds): ?>
                    <a href="<?= url('/post?type=need') ?>" class="btn btn-primary btn-block">
                        Post a Need
                    </a>
                <?php else: ?>
                    <p class="post-choice-restriction">
                        <span class="restriction-icon">🔒</span>
                        Only verified organizations can post needs.
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Offer -->
            <div class="post-choice-card">
                <div class="post-choice-icon">🟩</div>
                <h2>Post an Offer</h2>
                <p>Share surplus items, resources, or services with those who need them.</p>
                
                <a href="<?= url('/post?type=offer') ?>" class="btn btn-primary btn-block">
                    Post an Offer
                </a>
            </div>
            
            <!-- Volunteer -->
            <div class="post-choice-card <?= $canPostVolunteer ? '' : 'post-choice-card-disabled' ?>">
                <div class="post-choice-icon">🟦</div>
                <h2>Post Volunteer Opportunity</h2>
                <p>Recruit volunteers for your organization's programs and events.</p>
                
                <?php if ($canPostVolunteer): ?>
                    <a href="<?= url('/post?type=volunteer') ?>" class="btn btn-primary btn-block">
                        Post Opportunity
                    </a>
                <?php else: ?>
                    <p class="post-choice-restriction">
                        <span class="restriction-icon">🔒</span>
                        Only verified organizations can post volunteer opportunities.
                    </p>
                <?php endif; ?>
            </div>
            
        </div>
        
        <?php if (!$hasVerifiedOrg): ?>
            <div class="post-choice-org-note">
                <h3>Representing a Nonprofit?</h3>
                <p>
                    Verified organizations can post needs and volunteer opportunities. 
                    Contact us to register and verify your organization.
                </p>
            </div>
        <?php endif; ?>
        
    </div>
</section>
