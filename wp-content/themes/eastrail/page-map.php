<?php

// Template Name: Map
$groups = get_field('groups') ?: [];
$groupNames = wp_list_pluck($groups, 'name');
$geojson = get_field('geojson');
$geojson_url = $geojson['url'];
$geojson_file = get_attached_file($geojson['id']);

if (file_exists($geojson_file)) {
    $contents = file_get_contents($geojson_file);
    $geojson = json_decode($contents);
}

function get_feature_groups($geojson)
{
    $groups = [];

    foreach ($geojson->features as $feature) {
        $group = $feature->properties->group;
        if (!empty($group) && !in_array($group, $groups)) {
            $groups[] = $group;
        }
    }

    return $groups;
}

function get_group_features($geojson, $group_id = null)
{
    $features = array_filter($geojson->features, function ($feature) use ($group_id) {
        if ($group_id) {
            return !empty($feature->properties->group) && $feature->properties->group === $group_id;
        } else {
            return empty($feature->properties->group);
        }
    });

    return $features;
}

function get_feature_color($feature)
{
    if ($feature->properties->title === 'Eastrail System') {
        return 'black';
    }

    if (in_array($feature->geometry->type, ['LineString', 'MultiLineString']) && !empty($feature->properties->stroke)) {
        return $feature->properties->stroke;
    }

    if ($feature->geometry->type === 'Point' && !empty($feature->properties->{'marker-color'})) {
        return $feature->properties->{'marker-color'};
    }

    return '#3c77d4';
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo ET::theme_url('/static/favicon/apple-touch-icon.png'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo ET::theme_url('/static/favicon/favicon-32x32.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo ET::theme_url('/static/favicon/favicon-16x16.png'); ?>">
    <link rel="manifest" href="<?php echo ET::theme_url('/static/favicon/site.webmanifest'); ?>">
    <link rel="mask-icon" href="<?php echo ET::theme_url('/static/favicon/safari-pinned-tab.svg'); ?>" color="#00ae42">
    <link rel="shortcut icon" href="<?php echo ET::theme_url('/static/favicon/favicon.ico'); ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="<?php echo ET::theme_url('/static/favicon/browserconfig.xml'); ?>">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php wp_head(); ?>
    <?php echo get_field('head_tracking_codes', 'options'); ?>
</head>

<body <?php body_class(); ?>>
    <?php echo get_field('body_open_tracking_codes', 'options'); ?>

    <div style="display: none;">
        <?php include_once(ET::theme_path('static/icons/symbol/svg/sprite.symbol.svg')); ?>
    </div>

    <main id="main" class="map-embed" data-geojson="<?php echo $geojson_url; ?>">
        <div class="layers">
            <button class="expand">
                <span class="visually-hidden">Expand Sidebar</span>
                <svg class="icon">
                    <use xlink:href="#menu" />
                </svg>
            </button>
            <ul>
                <?php foreach ($groupNames as $group) : $features = get_group_features($geojson, $group); ?>
                    <li>
                        <div class="group-layer" data-group="<?php echo $group; ?>">
                            <button class="visibility">
                                <svg class="icon show">
                                    <use xlink:href="#show" />
                                </svg>
                                <svg class="icon hide">
                                    <use xlink:href="#hide" />
                                </svg>
                            </button>
                            <span><?php echo $group; ?></span>
                        </div>
                        <?php if ($features) : ?>
                            <ul>
                                <?php foreach ($features as $feature) : ?>
                                    <li>
                                        <div class="feature-layer" data-feature="<?php echo $feature->properties->id; ?>">
                                            <button style="color: <?php echo get_feature_color($feature); ?>">
                                                <?php if (in_array($feature->geometry->type, ['LineString', 'MultiLineString'])) : ?>
                                                    <svg class="icon">
                                                        <use xlink:href="#route" />
                                                    </svg>
                                                <?php else : ?>
                                                    <svg class="icon">
                                                        <use xlink:href="#map-marker" />
                                                    </svg>
                                                <?php endif; ?>
                                            </button>
                                            <span><?php echo $feature->properties->title; ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>

                <?php $ungrouped_featured = get_group_features($geojson); ?>
                <?php foreach ($ungrouped_featured as $feature) : ?>
                    <li>
                        <div class="feature-layer" data-feature="<?php echo $feature->properties->id; ?>">
                            <button style="color: <?php echo get_feature_color($feature); ?>">
                                <?php if ($feature->properties->title === 'Eastrail System') : ?>
                                    <svg class="icon">
                                        <use xlink:href="#search" />
                                    </svg>
                                <?php elseif ($feature->geometry->type === 'LineString') : ?>
                                    <svg class="icon">
                                        <use xlink:href="#route" />
                                    </svg>
                                <?php else : ?>
                                    <svg class="icon">
                                        <use xlink:href="#map-marker" />
                                    </svg>
                                <?php endif; ?>
                            </button>
                            <span><?php echo $feature->properties->title; ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="map"></div>
    </main>

    <?php wp_footer(); ?>
    <?php echo get_field('body_close_tracking_codes', 'options'); ?>
</body>

</html>
