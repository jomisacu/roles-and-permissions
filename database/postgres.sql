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
    context_id         varchar(36)                    not null,
    actor_id           char(36)                       not null,
    permission_id      char(36)                       not null,
    resource           text                           not null,
    resource_hash      char(64)                       not null,
    negated            boolean default false          not null,
    created_by_user_id char(36)                       null,
    created_at         timestamp(0) without time zone not null,
    updated_by_user_id char(36)                       null,
    updated_at         timestamp(0) without time zone null,
    constraint _jomisacu_actor_permission_relations__jomisacu_permiss_764190b4
        foreign key (permission_id) references _jomisacu_permissions (id)
            on delete cascade
);

create index _jomisacu_actor_permission_relations_context_id_actor_id_index
    on _jomisacu_actor_permission_relations (context_id, actor_id);

create unique index _jomisacu_actor_permission_relations_unique_rule
    on _jomisacu_actor_permission_relations (context_id, actor_id, permission_id, negated, resource_hash);

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
    context_id         char(36)                       not null,
    role_id            char(36)                       not null,
    actor_id           char(36)                       not null,
    created_by_user_id char(36)                       null,
    created_at         timestamp(0) without time zone not null,
    updated_by_user_id char(36)                       null,
    updated_at         timestamp(0) without time zone null,
    constraint _jomisacu_actor_role_relations__jomisacu_roles_id_fk
        foreign key (role_id) references _jomisacu_roles (id)
            on delete cascade
);

create index _jomisacu_actor_role_relations_context_id_role_id_acto_b321b87b
    on _jomisacu_actor_role_relations (context_id, role_id, actor_id);

create unique index _jomisacu_actor_role_relations_unique_assignment
    on _jomisacu_actor_role_relations (context_id, role_id, actor_id);

create table _jomisacu_role_permission_relations
(
    context_id         char(36)                       not null,
    role_id            char(36)                       not null,
    permission_id      char(36)                       not null,
    resource           text                           not null,
    resource_hash      char(64)                       not null,
    negated            boolean default false          not null,
    created_by_user_id char(36)                       null,
    created_at         timestamp(0) without time zone not null,
    updated_by_user_id char(36)                       null,
    updated_at         timestamp(0) without time zone null,
    constraint _jomisacu_role_permission_relations__jomisacu_permissions_id_fk
        foreign key (permission_id) references _jomisacu_permissions (id)
            on delete cascade,
    constraint _jomisacu_role_permission_relations__jomisacu_roles_id_fk
        foreign key (role_id) references _jomisacu_roles (id)
            on delete cascade
);

create index _jomisacu_role_permission_relations_context_id_role_id_index
    on _jomisacu_role_permission_relations (context_id, role_id);

create unique index _jomisacu_role_permission_relations_unique_rule
    on _jomisacu_role_permission_relations (context_id, role_id, permission_id, negated, resource_hash);

create index _jomisacu_roles_context_id_index
    on _jomisacu_roles (context_id);
