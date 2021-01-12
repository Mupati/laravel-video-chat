<template>
  <main>
    <div class="container">
      <div class="row">
        <div class="col-12 text-center">
          <img src="img/agora-logo.png" alt="Agora Logo" class="img-fuild" />
        </div>
      </div>
    </div>
    <section id="login-form" v-if="!isLoggedIn">
      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-6 offset-sm-3">
            <form>
              <div class="mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" class="form-control" v-model="name" />
              </div>
              <div class="mb-3">
                <label class="form-label">Room Name</label>
                <input type="text" class="form-control" v-model="room" />
              </div>
              <div
                class="alert alert-warning alert-dismissible fade show"
                role="alert"
                v-if="isError"
              >
                Invalid room name or password
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="alert"
                  aria-label="Close"
                ></button>
              </div>
              <div class="text-center">
                <button
                  class="btn btn-primary text-center"
                  @click.prevent="joinRoom"
                  :disabled="room === null"
                >
                  Join Call
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
    <section id="video-container" v-else>
      <div id="local-video"></div>
      <div id="remote-video"></div>

      <div class="action-btns">
        <button type="button" class="btn btn-info" @click="handleAudioToggle">
          {{ mutedAudio ? "Unmute" : "Mute" }}
        </button>
        <button
          type="button"
          class="btn btn-primary mx-4"
          @click="handleVideoToggle"
        >
          {{ mutedVideo ? "ShowVideo" : "HideVideo" }}
        </button>
        <button type="button" class="btn btn-danger" @click="endCall">
          EndCall
        </button>
      </div>
    </section>
  </main>
</template>

<script>
export default {
  name: "AgoraChat",
  data() {
    return {
      isLoggedIn: false,
      client: null,
      name: null,
      room: null,
      password: null,
      isError: false,
      localStream: null,
      mutedAudio: false,
      mutedVideo: false,
    };
  },

  created() {
    this.initializeAgora();
    // this.joinRoom();
  },

  methods: {
    generateToken() {
      return axios.get("/api/generate-agora-token");
      // .then((res) => {
      //   console.log(res);
      //   return res.data.token;
      // })
      // .catch((err) => {
      //   console.log(err);
      // });
    },
    initializeAgora() {
      this.client = AgoraRTC.createClient({ mode: "rtc", codec: "h264" });
      this.client.init(
        "396e04646ef344e5a6c69304f56f59c0",
        () => {
          console.log("AgoraRTC client initialized");
          this.joinRoom();
        },
        (err) => {
          console.log("AgoraRTC client init failed", err);
        }
      );
    },

    async joinRoom() {
      console.log("Join Room");
      const tokenRes = await this.generateToken();
      console.log(tokenRes.data.token);
      this.client.join(
        tokenRes.data.token,
        "mupati",
        0,
        (uid) => {
          console.log("User " + uid + " join channel successfully");
          this.isLoggedIn = true;
          this.createLocalStream();
          this.initializedAgoraListeners();
        },
        (err) => {
          console.log("Join channel failed", err);
          this.setErrorMessage();
        }
      );
    },

    initializedAgoraListeners() {
      //   Register event listeners
      this.client.on("stream-published", function (evt) {
        console.log("Publish local stream successfully");
        console.log(evt);
      });

      //subscribe remote stream
      this.client.on("stream-added", ({ stream }) => {
        console.log("New stream added: " + stream.getId());
        this.client.subscribe(stream, function (err) {
          console.log("Subscribe stream failed", err);
        });
      });

      this.client.on("stream-subscribed", (evt) => {
        // Attach remote stream to the remote-video div
        evt.stream.play("remote-video");
        this.client.publish(evt.stream);
      });

      this.client.on("stream-removed", ({ stream }) => {
        console.log(String(stream.getId()));
        stream.close();
      });

      this.client.on("peer-online", (evt) => {
        console.log("peer-online", evt.uid);
      });

      this.client.on("peer-leave", (evt) => {
        var uid = evt.uid;
        var reason = evt.reason;
        console.log("remote user left ", uid, "reason: ", reason);
      });

      this.client.on("stream-unpublished", (evt) => {
        console.log(evt);
      });
    },

    createLocalStream() {
      this.localStream = AgoraRTC.createStream({
        audio: true,
        video: true,
      });

      // Initialize the local stream
      this.localStream.init(
        () => {
          // Play the local stream
          this.localStream.play("local-video");
          // Publish the local stream
          this.client.publish(this.localStream, (err) => {
            console.log("publish local stream", err);
          });
        },
        (err) => {
          console.log(err);
        }
      );
    },

    endCall() {
      this.localStream.close();
      this.client.leave(
        () => {
          console.log("Leave channel successfully");
          this.isLoggedIn = false;
        },
        (err) => {
          console.log("Leave channel failed");
        }
      );
    },

    setErrorMessage() {
      this.isError = true;
      setTimeout(() => {
        this.isError = false;
      }, 2000);
    },

    handleAudioToggle() {
      if (this.mutedAudio) {
        this.localStream.enableAudio();
        this.mutedAudio = false;
      } else {
        this.localStream.disableAudio();
        this.mutedAudio = true;
      }
    },

    handleVideoToggle() {
      if (this.mutedVideo) {
        this.localStream.enableVideo();
        this.mutedVideo = false;
      } else {
        this.localStream.disableVideo();
        this.mutedVideo = true;
      }
    },
  },
};
</script>

<style scoped>
main {
  margin-top: 50px;
}

#video-container {
  width: 700px;
  height: 500px;
  max-width: 90vw;
  max-height: 50vh;
  margin: 0 auto;
  border: 1px solid #099dfd;
  position: relative;
  box-shadow: 1px 1px 11px #9e9e9e;
  background-color: #fff;
}

#local-video {
  width: 30%;
  height: 30%;
  position: absolute;
  left: 10px;
  bottom: 10px;
  border: 1px solid #fff;
  border-radius: 6px;
  z-index: 2;
  cursor: pointer;
}

#remote-video {
  width: 100%;
  height: 100%;
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  top: 0;
  z-index: 1;
  margin: 0;
  padding: 0;
  cursor: pointer;
}

.action-btns {
  position: absolute;
  bottom: 20px;
  left: 50%;
  margin-left: -50px;
  z-index: 3;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
}

#login-form {
  margin-top: 100px;
}
</style>