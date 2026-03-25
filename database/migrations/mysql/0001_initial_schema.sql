create table _jomisacu_permissions
(
    id          char(36)     not null
        primary key,
    context_id  char(36)     not null,
    name        varchar(191) not null,
    description text         null
);

create table _jomisacu_actor_permission_relations
(
    context_id         varchar(36)          not null,
    actor_id           char(36)             not null,
    permission_id      char(36)             not null,
    resource           text                 not null,
    negated            tinyint(1) default 0 not null,
    created_by_user_id char(36)             null,
    created_at         datetime             not null,
    updated_by_user_id char(36)             null,
    updated_at         datetime             null,
    constraint _jomisacu_actor_permission_relations__jomisacu_permissions_id_fk
        foreign key (permission_id) references _jomisacu_permissions (id)
            on delete cascade
);

create index _jomisacu_actor_permission_relations_context_id_actor_id_index
    on _jomisacu_actor_permission_relations (context_id, actor_id);

create index _jomisacu_permissions_context_id_index
    on _jomisacu_permissions (context_id);

create table _jomisacu_roles
(
    id          char(36)     not null
        primary key,
    context_id  char(36)     not null,
    name        varchar(191) not null,
    description text         null
);

create table _jomisacu_actor_role_relations
(
    context_id         char(36) not null,
    role_id            char(36) not null,
    actor_id           char(36) not null,
    created_by_user_id char(36) null,
    created_at         datetime not null,
    updated_by_user_id char(36) null,
    constraint _jomisacu_actor_role_relations__jomisacu_roles_id_fk
        foreign key (role_id) references _jomisacu_roles (id)
            on delete cascade
);

create index _jomisacu_actor_role_relations_context_id_role_id_actor_id_index
    on _jomisacu_actor_role_relations (context_id, role_id, actor_id);

create table _jomisacu_role_permission_relations
(
    context_id         char(36)             not null,
    role_id            char(36)             not null,
    permission_id      char(36)             not null,
    resource           text                 not null,
    negated            tinyint(1) default 0 not null,
    created_by_user_id char(36)             null,
    created_at         datetime             not null,
    updated_by_user_id char(36)             null,
    updated_at         datetime             null,
    constraint _jomisacu_role_permission_relations__jomisacu_permissions_id_fk
        foreign key (permission_id) references _jomisacu_permissions (id)
            on delete cascade,
    constraint _jomisacu_role_permission_relations__jomisacu_roles_id_fk
        foreign key (role_id) references _jomisacu_roles (id)
            on delete cascade
);

create index _jomisacu_role_permission_relations_context_id_role_id_index
    on _jomisacu_role_permission_relations (context_id, role_id);

create index _jomisacu_roles_context_id_index
    on _jomisacu_roles (context_id);
