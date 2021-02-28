<template>
  <div class="container">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <button class="btn btn-success" @click="startStream">
          Start Stream</button
        ><br />
        <p v-if="isVisibleLink" class="my-5">
          Share the following streaming link: {{ streamLink }}
        </p>
        <video autoplay ref="broadcaster"></video>
      </div>
    </div>
  </div>
</template>

<script>
import Peer from "simple-peer";
export default {
  name: "Broadcaster",
  props: [
    "auth_user_id",
    "env",
    "turn_url",
    "turn_username",
    "turn_credential",
  ],
  data() {
    return {
      isVisibleLink: false,
      streamingPresenceChannel: null,
      streamingUsers: [],
      currentlyContactedUser: null,
      allPeers: {}, // this will hold all dynamically created peers using the 'ID' of users who just joined as keys
    };
  },

  computed: {
    streamId() {
      // you can improve streamId generation code. As long as we include the
      // broadcaster's user id, we are assured of getting unique streamiing link everytime.
      // the current code just generates a fixed streaming link for a particular user.
      return `${this.auth_user_id}12acde2`;
    },

    streamLink() {
      // just a quick fix. can be improved by setting the app_url
      if (this.env === "production") {
        return `https://laravel-video-call.herokuapp.com/streaming/${this.streamId}`;
      } else {
        return `http://127.0.0.1:8000/streaming/${this.streamId}`;
      }
    },
  },

  methods: {
    async startStream() {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: true,
      });
      this.$refs.broadcaster.srcObject = stream;

      this.initializeStreamingChannel();
      this.isVisibleLink = true;
    },

    peerCreator(stream) {
      let peer;
      let offer;
      return {
        create: () => {
          peer = new Peer({
            initiator: true,
            trickle: false,
            stream: stream,
            config: {
              iceServers: [
                {
                  urls: "stun:stun.stunprotocol.org",
                },
                {
                  urls: this.turn_url,
                  username: this.turn_username,
                  credential: this.turn_credential,
                },
              ],
            },
          });
        },

        getPeer: () => peer,

        initEvents: () => {
          peer.on("signal", (data) => {
            offer = data;
            // send offer over here. Better than the 5 seconds delay used in the streaming presence channel
          });

          peer.on("stream", (stream) => {
            console.log("onStream");
          });

          peer.on("track", (track, stream) => {
            console.log("onTrack");
          });

          peer.on("connect", () => {
            console.log("Broadcaster Peer connected");
          });

          peer.on("close", () => {
            console.log("Broadcaster Peer closed");
          });

          peer.on("error", (err) => {
            console.log("handle error gracefully");
          });
        },

        getOffer: () => offer,
      };
    },

    initializeStreamingChannel() {
      this.streamingPresenceChannel = window.Echo.join(
        `streaming-channel.${this.streamId}`
      );

      this.streamingPresenceChannel.here((users) => {
        this.streamingUsers = users;
      });

      this.streamingPresenceChannel.joining((user) => {
        console.log("New User", user);
        // if this new user is not already on the call, send your stream offer
        const joiningUserIndex = this.streamingUsers.findIndex(
          (data) => data.id === user.id
        );
        if (joiningUserIndex < 0) {
          this.streamingUsers.push(user);

          // A new user just joined the channel so signal that user
          this.currentlyContactedUser = user.id;

          this.$set(
            this.allPeers,
            `${user.id}`,
            this.peerCreator(this.$refs.broadcaster.srcObject)
          );
          // Create Peer
          this.allPeers[user.id].create();

          // Initialize Events
          this.allPeers[user.id].initEvents();

          // allow 5 seconds before you send offer request.
          // offer will be ready by then
          // feels like there is a better way to go about this. if you see this, suggest a better way in a PR

          // maybe pass

          console.log("about to send a stream offer");
          setTimeout(() => {
            axios
              .post("/stream-offer", {
                // The broadcaster is the first to join the channel
                broadcaster: this.streamingUsers[0],
                receiver: user,
                offer: this.allPeers[user.id].getOffer(),
                streamId: this.streamId,
              })
              .then((res) => {
                console.log(res);
              })
              .catch((err) => {
                console.log(err);
              });
          }, 5000);
        }
      });

      this.streamingPresenceChannel.leaving((user) => {
        console.log("Leaving: ", user);
      });

      this.streamingPresenceChannel.listen("StreamAnswer", ({ data }) => {
        console.log("answer", data);

        // edit the received signal and signal broadcaster
        // ++ Broadcaster should signal viewer with the answer based on the
        // user id  that comes with the viewer signal

        if (data.answer.renegotiate) {
          console.log("renegotating");
        }
        if (data.answer.sdp) {
          const updatedSignal = {
            ...data.answer,
            sdp: `${data.answer.sdp}\n`,
          };

          this.allPeers[this.currentlyContactedUser]
            .getPeer()
            .signal(updatedSignal);
        }
      });
    },
  },
};
</script>

<style scoped>
</style>